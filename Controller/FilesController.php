<?php

/**
 * This file is part of the ChillDev FileManager bundle.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.1
 * @since 0.0.1
 * @package ChillDev\Bundle\FileManagerBundle
 */

namespace ChillDev\Bundle\FileManagerBundle\Controller;

use DateTime;

use ChillDev\Bundle\FileManagerBundle\Filesystem\Disk;
use ChillDev\Bundle\FileManagerBundle\Form\Type\MkdirType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Files controller.
 *
 * @Route("/files")
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.1
 * @since 0.0.1
 * @package ChillDev\Bundle\FileManagerBundle
 */
class FilesController extends Controller
{
    /**
     * File download action.
     *
     * @Route(
     *      "/download/{disk}/{path}",
     *      name="chilldev_filemanager_files_download",
     *      requirements={"path"=".*"}
     *  )
     * @param Disk $disk Disk scope.
     * @param string $path Destination directory.
     * @return StreamedResponse File download disposition.
     * @version 0.0.1
     * @since 0.0.1
     */
    public function downloadAction(Disk $disk, $path)
    {
        // make sure it's absolute path
        $path = '/' . $path . '/';

        // resolve all symbolic references
        $path = \preg_replace('#//+#', '/', $path);
        while (\preg_match('#/([^/]+/\\.)?\\./#', $path, $match, \PREG_OFFSET_CAPTURE) > 0) {
            $path = \substr_replace($path, '/', $match[0][1], \strlen($match[0][0]));
        }

        // reference outside disk scope
        if (\strpos($path, '/../') !== false) {
            throw new HttpException(400, 'File path contains invalid reference that exceeds disk scope.');
        }

        $path = \substr($path, 1, -1);

        // access file - very primitive way for now, needs abstraction in future
        $realpath = \realpath($disk->getSource() . $path);

        // non-existing path
        if (!$realpath) {
            throw new NotFoundHttpException(\sprintf('File "%s/%s" does not exist.', $disk, $path));
        }

        if (!\is_file($realpath)) {
            throw new HttpException(
                400,
                \sprintf('"%s/%s" is not a regular file that can be downloaded.', $disk, $path)
            );
        }

        // set up cache information
        $time = \filemtime($realpath);
        $request = $this->getRequest();
        $response = new StreamedResponse();
        $response->setLastModified(DateTime::createFromFormat('U', $time))
            ->setETag(\sha1($disk . $path . '/' . $time));

        $response->headers->set(
            'Content-Disposition',
            $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, \basename($path))
        );
        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Content-Length', \filesize($realpath));

        // return cached response
        if ($response->isNotModified($request)) {
            return $response;
        }

        $response->setCallback(
            function () use ($realpath) {
                \readfile($realpath);
            }
        );
        return $response;
    }

    /**
     * File delete action.
     *
     * @Route(
     *      "/delete/{disk}/{path}",
     *      name="chilldev_filemanager_files_delete",
     *      requirements={"path"=".*"}
     *  )
     * @Method("POST")
     * @param Disk $disk Disk scope.
     * @param string $path Subject file.
     * @return Symfony\Component\HttpFoundation\RedirectResponse Redirect to browse view.
     * @version 0.0.1
     * @since 0.0.1
     */
    public function deleteAction(Disk $disk, $path)
    {
        // make sure it's absolute path
        $path = '/' . $path . '/';

        // resolve all symbolic references
        $path = \preg_replace('#//+#', '/', $path);
        while (\preg_match('#/([^/]+/\\.)?\\./#', $path, $match, \PREG_OFFSET_CAPTURE) > 0) {
            $path = \substr_replace($path, '/', $match[0][1], \strlen($match[0][0]));
        }

        // reference outside disk scope
        if (\strpos($path, '/../') !== false) {
            throw new HttpException(400, 'File path contains invalid reference that exceeds disk scope.');
        }

        $path = \substr($path, 1, -1);

        // access file - very primitive way for now, needs abstraction in future
        $realpath = \realpath($disk->getSource() . $path);
        $diskpath = $disk . '/' . $path;

        // non-existing path
        if (!$realpath) {
            throw new NotFoundHttpException(\sprintf('File "%s" does not exist.', $diskpath));
        }

        if (!\is_file($realpath)) {
            throw new HttpException(400, \sprintf('"%s" is not a regular file that can be deleted.', $diskpath));
        }

        \unlink($realpath);

        $this->get('logger')->info(
            \sprintf('File "%s" deleted by user "%s".', $diskpath, $this->getUser()),
            ['realpath' => $realpath, 'scope' => $disk->getSource()]
        );
        $this->get('session')->getFlashBag()->add(
            'done',
            $this->get('translator')->trans('"%file%" has been deleted.', ['%file%' => $diskpath])
        );

        // move back to directory view
        return $this->redirect(
            $this->generateUrl(
                'chilldev_filemanager_disks_browse',
                ['disk' => $disk->getId(), 'path' => \dirname($path)]
            )
        );
    }

    /**
     * Directory creation action.
     *
     * @Route(
     *      "/mkdir/{disk}/{path}",
     *      name="chilldev_filemanager_files_mkdir",
     *      requirements={"path"=".*"},
     *      defaults={"path"=""}
     *  )
     * @param Disk $disk Disk scope.
     * @param string $path Destination location.
     * @return Response Result response.
     * @version 0.0.1
     * @since 0.0.1
     */
    public function mkdirAction(Disk $disk, $path = '')
    {
        // make sure it's absolute path
        $path = '/' . $path . '/';

        // resolve all symbolic references
        $path = \preg_replace('#//+#', '/', $path);
        while (\preg_match('#/([^/]+/\\.)?\\./#', $path, $match, \PREG_OFFSET_CAPTURE) > 0) {
            $path = \substr_replace($path, '/', $match[0][1], \strlen($match[0][0]));
        }

        // reference outside disk scope
        if (\strpos($path, '/../') !== false) {
            throw new HttpException(400, 'Directory path contains invalid reference that exceeds disk scope.');
        }

        $path = \substr($path, 1, -1);

        // access file - very primitive way for now, needs abstraction in future
        $realpath = \realpath($disk->getSource() . $path);
        $diskpath = $disk . '/' . $path;

        // non-existing path
        if (!$realpath) {
            throw new NotFoundHttpException(\sprintf('Directory "%s" does not exist.', $diskpath));
        }

        if (!\is_dir($realpath)) {
            throw new HttpException(
                400,
                \sprintf('"%s" is not a directory, so a sub-directory can\'t be created within it.', $diskpath)
            );
        }

        $request = $this->getRequest();

        // initialize form
        $form = $this->createForm(new MkdirType($realpath), ['name' => null]);

        // only handle POST form submits
        if ($request->isMethod('POST')) {
            $form->bind($request);

            // validate form
            if ($form->isValid()) {
                $data = $form->getData();

                \mkdir($realpath . '/' . $data['name']);

                $fullpath = $diskpath . '/' . $data['name'];
                $this->get('logger')->info(
                    \sprintf('Directory "%s" created by user "%s".', $fullpath, $this->getUser()),
                    ['realpath' => $realpath, 'scope' => $disk->getSource()]
                );
                $this->get('session')->getFlashBag()->add(
                    'done',
                    $this->get('translator')->trans('"%directory%" has been created.', ['%directory%' => $fullpath])
                );

                // move back to directory view
                return $this->redirect(
                    $this->generateUrl('chilldev_filemanager_disks_browse', ['disk' => $disk->getId(), 'path' => $path])
                );
            }
        }

        // render form view
        return $this->render(
            'ChillDevFileManagerBundle:Files:mkdir.html.config',
            ['disk' => $disk, 'path' => $path, 'form' => $form->createView()]
        );
    }
}
