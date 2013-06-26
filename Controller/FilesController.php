<?php

/**
 * This file is part of the ChillDev FileManager bundle.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012 - 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.1
 * @since 0.0.2
 * @package ChillDev\Bundle\FileManagerBundle
 */

namespace ChillDev\Bundle\FileManagerBundle\Controller;

use DateTime;

use ChillDev\Bundle\FileManagerBundle\Filesystem\Disk;
use ChillDev\Bundle\FileManagerBundle\Form\Type\MkdirType;
use ChillDev\Bundle\FileManagerBundle\Form\Type\RenameType;
use ChillDev\Bundle\FileManagerBundle\Form\Type\UploadType;
use ChillDev\Bundle\FileManagerBundle\Utils\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use Symfony\Component\HttpFoundation\Request;
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
 * @copyright 2012 - 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.1
 * @since 0.0.1
 * @package ChillDev\Bundle\FileManagerBundle
 */
class FilesController extends BaseController
{
    /**
     * File download action.
     *
     * @Route(
     *      "/download/{disk}/{path}",
     *      name="chilldev_filemanager_files_download",
     *      requirements={"path"=".*"}
     *  )
     * @param Request $request Current request.
     * @param Disk $disk Disk scope.
     * @param string $path Destination directory.
     * @return StreamedResponse File download disposition.
     * @throws HttpException When requested path is invalid or is not a file.
     * @throws NotFoundHttpException When requested path does not exist.
     * @version 0.0.3
     * @since 0.0.1
     */
    public function downloadAction(Request $request, Disk $disk, $path)
    {
        $path = Controller::resolvePath($path);

        // get filesystem from given disk
        $filesystem = $disk->getFilesystem();

        Controller::ensureExist($disk, $filesystem, $path);

        // file information object
        $info = $filesystem->getFileInfo($path);

        if (!$info->isFile()) {
            throw new HttpException(
                400,
                \sprintf('"%s/%s" is not a regular file that can be downloaded.', $disk, $path)
            );
        }

        // set up cache information
        $time = $info->getMTime();
        $response = new StreamedResponse();
        $response->setLastModified(DateTime::createFromFormat('U', $time))
            ->setETag(\sha1($disk . $path . '/' . $time));

        $response->headers->set(
            'Content-Disposition',
            $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                \iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', \basename($path))
            )
        );
        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Content-Length', $info->getSize());

        // return cached response
        if ($response->isNotModified($request)) {
            return $response;
        }

        $response->setCallback(
            function () use ($info) {
                $info->openFile()->fpassthru();
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
     * @throws HttpException When requested path is invalid or is not a file.
     * @throws NotFoundHttpException When requested path does not exist.
     * @version 0.1.1
     * @since 0.0.1
     */
    public function deleteAction(Disk $disk, $path)
    {
        $path = Controller::resolvePath($path);

        // get filesystem from given disk
        $filesystem = $disk->getFilesystem();
        $diskpath = $disk . '/' . $path;

        Controller::ensureExist($disk, $filesystem, $path);

        $filesystem->remove($path);

        $this->generateSuccessMessage($disk, '"%s" has been deleted', ['%file%' => $diskpath]);

        // move back to directory view
        return $this->redirectToDirectory($disk, \dirname($path));
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
     * @param Request $request Current request.
     * @param Disk $disk Disk scope.
     * @param string $path Destination location.
     * @return Response Result response.
     * @throws HttpException When requested path is invalid or is not a directory.
     * @throws NotFoundHttpException When requested path does not exist.
     * @version 0.1.1
     * @since 0.0.1
     */
    public function mkdirAction(Request $request, Disk $disk, $path = '')
    {
        $path = Controller::resolvePath($path);

        // get filesystem from given disk
        $filesystem = $disk->getFilesystem();
        $diskpath = $disk . '/' . $path;

        Controller::ensureExist($disk, $filesystem, $path);

        // file information object
        $info = $filesystem->getFileInfo($path);

        if (!$info->isDir()) {
            throw new HttpException(
                400,
                \sprintf('"%s" is not a directory, so a sub-directory can\'t be created within it.', $diskpath)
            );
        }

        // initialize form
        $form = $this->createForm(new MkdirType($filesystem, $path), ['name' => null]);

        // only handle POST form submits
        if ($request->isMethod('POST')) {
            $form->bind($request);

            // validate form
            if ($form->isValid()) {
                $data = $form->getData();
                $fullpath = $path . '/' . $data['name'];

                $filesystem->mkdir($fullpath);

                $this->generateSuccessMessage($disk, '"%s" has been created', ['%file%' => $disk . '/' . $fullpath]);

                // move back to directory view
                return $this->redirectToDirectory($disk, $path);
            }
        }

        // render form view
        return $this->render(
            'ChillDevFileManagerBundle:Files:mkdir.html.default',
            ['disk' => $disk, 'path' => $path, 'form' => $form->createView()]
        );
    }

    /**
     * Handles file upload.
     *
     * @Route(
     *      "/upload/{disk}/{path}",
     *      name="chilldev_filemanager_files_upload",
     *      requirements={"path"=".*"},
     *      defaults={"path"=""}
     *  )
     * @param Request $request Current request.
     * @param Disk $disk Disk scope.
     * @param string $path Destination location.
     * @return Response Result response.
     * @throws HttpException When requested path is invalid or is not a directory.
     * @throws NotFoundHttpException When requested path does not exist.
     * @version 0.1.1
     * @since 0.0.3
     */
    public function uploadAction(Request $request, Disk $disk, $path = '')
    {
        $path = Controller::resolvePath($path);

        // get filesystem from given disk
        $filesystem = $disk->getFilesystem();
        $diskpath = $disk . '/' . $path;

        Controller::ensureExist($disk, $filesystem, $path);

        // file information object
        $info = $filesystem->getFileInfo($path);

        if (!$info->isDir()) {
            throw new HttpException(
                400,
                \sprintf('"%s" is not a directory, so a file can\'t be uploaded into it.', $diskpath)
            );
        }

        // initialize form
        $form = $this->createForm(
            new UploadType($filesystem, $path),
            ['name' => null, 'file' => null, 'force' => null]
        );

        // only handle POST form submits
        if ($request->isMethod('POST')) {
            $form->bind($request);

            // validate form
            if ($form->isValid()) {
                $data = $form->getData();
                $fullpath = $path . '/' . $data['name'];

                $filesystem->upload($path, $data['file'], $data['name']);

                $this->generateSuccessMessage($disk, '"%s" has been uploaded', ['%file%' => $disk . '/' . $fullpath]);

                // move back to directory view
                return $this->redirectToDirectory($disk, $path);
            }
        }

        // render form view
        return $this->render(
            'ChillDevFileManagerBundle:Files:upload.html.default',
            ['disk' => $disk, 'path' => $path, 'form' => $form->createView()]
        );
    }

    /**
     * File renaming action.
     *
     * @Route(
     *      "/rename/{disk}/{path}",
     *      name="chilldev_filemanager_files_rename",
     *      requirements={"path"=".*"}
     *  )
     * @param Request $request Current request.
     * @param Disk $disk Disk scope.
     * @param string $path File being renamed.
     * @return Response Result response.
     * @throws HttpException When requested path is invalid.
     * @throws NotFoundHttpException When requested path does not exist.
     * @version 0.1.1
     * @since 0.0.3
     */
    public function renameAction(Request $request, Disk $disk, $path)
    {
        $path = Controller::resolvePath($path);

        // get filesystem from given disk
        $filesystem = $disk->getFilesystem();
        $diskpath = $disk . '/' . $path;

        Controller::ensureExist($disk, $filesystem, $path);

        // initialize form
        $form = $this->createForm(new RenameType($filesystem, $path), ['name' => null]);

        // only handle POST form submits
        if ($request->isMethod('POST')) {
            $form->bind($request);

            // validate form
            if ($form->isValid()) {
                $data = $form->getData();
                $dirpath = \dirname($path);
                $fullpath = $dirpath . '/' . $data['name'];

                $filesystem->move($path, $fullpath);

                $this->generateSuccessMessage(
                    $disk,
                    '"%s" has been renamed to "%s"',
                    ['%file%' => $diskpath, '%name%' => $data['name']]
                );

                // move back to directory view
                return $this->redirectToDirectory($disk, $dirpath);
            }
        }

        // render form view
        return $this->render(
            'ChillDevFileManagerBundle:Files:rename.html.default',
            ['disk' => $disk, 'path' => $path, 'form' => $form->createView()]
        );
    }

    /**
     * File moving action.
     *
     * @Route(
     *      "/move/{disk}/{path}:{destination}",
     *      name="chilldev_filemanager_files_move",
     *      requirements={"path"="[^:]*", "destination"=".*"},
     *      defaults={"destination"=""}
     *  )
     * @param Request $request Current request.
     * @param Disk $disk Disk scope.
     * @param string $path File being moved.
     * @param string $destination Destination location.
     * @return Response Result response.
     * @throws HttpException When requested path is invalid.
     * @throws NotFoundHttpException When requested path does not exist.
     * @version 0.1.1
     * @since 0.0.3
     */
    public function moveAction(Request $request, Disk $disk, $path, $destination = '')
    {
        $path = Controller::resolvePath($path);
        $destination = Controller::resolvePath($destination);

        // get filesystem from given disk
        $filesystem = $disk->getFilesystem();
        $diskpath = $disk . '/' . $path;
        $diskdestination = $disk . '/' . $destination;

        Controller::ensureExist($disk, $filesystem, $path, $destination);

        // file information object
        $info = $filesystem->getFileInfo($destination);

        if (!$info->isDir()) {
            throw new HttpException(400, \sprintf('"%s/%s" is not a directory.', $disk, $destination));
        }

        // only handle POST form submits
        if ($request->isMethod('POST')) {
            $name = \basename($path);
            $filesystem->move($path, $destination . '/' . $name);

            $this->generateSuccessMessage(
                $disk,
                '"%s" has been moved to "%s"',
                ['%file%' => $diskpath, '%destination%' => $diskdestination]
            );

            // move back to directory view
            return $this->redirectToDirectory($disk, \dirname($path));
        }

        $list = [];

        foreach ($filesystem->createDirectoryIterator($destination) as $file => $info) {
            // only choose from directories
            if ($info->isDir()) {
                $list[$file] = [
                    'isDirectory' => true,
                    'path' => $destination . '/' . $file,
                ];
            }
        }

        // perform sorting
        \uasort($list, Controller::getSorter('path', $request->query->get('order', 1)));

        // render destination choice view
        return $this->render(
            'ChillDevFileManagerBundle:Files:destination.html.default',
            [
                'disk' => $disk,
                'path' => $path,
                'destination' => $destination,
                'route' => 'chilldev_filemanager_files_move',
                'title' => 'Moving file %disk%/%path%',
                'list' => $list,
            ]
        );
    }

    /**
     * File copying action.
     *
     * @Route(
     *      "/copy/{disk}/{path}:{destination}",
     *      name="chilldev_filemanager_files_copy",
     *      requirements={"path"="[^:]*", "destination"=".*"},
     *      defaults={"destination"=""}
     *  )
     * @param Request $request Current request.
     * @param Disk $disk Disk scope.
     * @param string $path File being copied.
     * @param string $destination Destination location.
     * @return Response Result response.
     * @throws HttpException When requested path is invalid.
     * @throws NotFoundHttpException When requested path does not exist.
     * @version 0.1.1
     * @since 0.0.3
     */
    public function copyAction(Request $request, Disk $disk, $path, $destination = '')
    {
        $path = Controller::resolvePath($path);
        $destination = Controller::resolvePath($destination);

        // get filesystem from given disk
        $filesystem = $disk->getFilesystem();
        $diskpath = $disk . '/' . $path;
        $diskdestination = $disk . '/' . $destination;

        Controller::ensureExist($disk, $filesystem, $path, $destination);

        // file information object
        $info = $filesystem->getFileInfo($destination);

        if (!$info->isDir()) {
            throw new HttpException(400, \sprintf('"%s/%s" is not a directory.', $disk, $destination));
        }

        // only handle POST form submits
        if ($request->isMethod('POST')) {
            $name = \basename($path);
            $filesystem->copy($path, $destination . '/' . $name);

            $this->generateSuccessMessage(
                $disk,
                '"%s" has been copied to "%s"',
                ['%file%' => $diskpath, '%destination%' => $diskdestination]
            );

            // move back to directory view
            return $this->redirectToDirectory($disk, \dirname($path));
        }

        $list = [];

        foreach ($filesystem->createDirectoryIterator($destination) as $file => $info) {
            // only choose from directories
            if ($info->isDir()) {
                $list[$file] = [
                    'isDirectory' => true,
                    'path' => $destination . '/' . $file,
                ];
            }
        }

        // perform sorting
        \uasort($list, Controller::getSorter('path', $request->query->get('order', 1)));

        // render destination choice view
        return $this->render(
            'ChillDevFileManagerBundle:Files:destination.html.default',
            [
                'disk' => $disk,
                'path' => $path,
                'destination' => $destination,
                'route' => 'chilldev_filemanager_files_copy',
                'title' => 'Copying file %disk%/%path%',
                'list' => $list,
            ]
        );
    }

    /**
     * Adds session flash message.
     *
     * @param string $type Message type.
     * @param string $message Message template.
     * @param array $params Message parameters.
     * @version 0.1.1
     * @since 0.1.1
     */
    protected function addFlashMessage($type, $message, array $params = [])
    {
        $this->get('session')->getFlashBag()->add(
            $type,
            $this->get('translator')->trans($message, $params)
        );
    }

    /**
     * Redirects to given directory.
     *
     * @param Disk $disk Disk.
     * @param string $path Directory path.
     * @return Symfony\Component\HttpFoundation\RedirectResponse Redirect to browse view.
     * @version 0.1.1
     * @since 0.1.1
     */
    protected function redirectToDirectory(Disk $disk, $path)
    {
        return $this->redirect(
            $this->generateUrl(
                'chilldev_filemanager_disks_browse',
                ['disk' => $disk->getId(), 'path' => $path]
            )
        );
    }

    /**
     * Generates success message for logger and flash messager.
     *
     * @param Disk $disk Current disk scope.
     * @param string $message Message pattern.
     * @param array $params Message parameters.
     * @version 0.1.1
     * @since 0.1.1
     */
    protected function generateSuccessMessage(Disk $disk, $message, array $params = [])
    {
        $this->get('logger')->info(
            \vsprintf($message . ' by user "%s".', \array_merge(\array_values($params), [$this->getUser()])),
            ['scope' => $disk->getSource()]
        );

        // re-format pattern for named placeholders
        $message = \vsprintf($message . '.', \array_keys($params));

        $this->addFlashMessage('done', $message, $params);
    }
}
