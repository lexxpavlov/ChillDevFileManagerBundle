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
     * @param Disk $disk Disk scope.
     * @param string $path Destination directory.
     * @return StreamedResponse File download disposition.
     * @throws HttpException When requested path is invalid or is not a file.
     * @throws NotFoundHttpException When requested path does not exist.
     * @version 0.0.3
     * @since 0.0.1
     */
    public function downloadAction(Disk $disk, $path)
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
        $request = $this->getRequest();
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

        $this->get('logger')->info(
            \sprintf('File "%s" deleted by user "%s".', $path, $this->getUser()),
            ['scope' => $disk->getSource()]
        );
        $this->addFlashMessage('done', '"%file%" has been deleted.', ['%file%' => $diskpath]);

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
     * @param Disk $disk Disk scope.
     * @param string $path Destination location.
     * @return Response Result response.
     * @throws HttpException When requested path is invalid or is not a directory.
     * @throws NotFoundHttpException When requested path does not exist.
     * @version 0.1.1
     * @since 0.0.1
     */
    public function mkdirAction(Disk $disk, $path = '')
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

        $request = $this->getRequest();

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

                $this->get('logger')->info(
                    \sprintf('Directory "%s" created by user "%s".', $fullpath, $this->getUser()),
                    ['scope' => $disk->getSource()]
                );
                $this->addFlashMessage(
                    'done',
                    '"%directory%" has been created.',
                    ['%directory%' => $disk . '/' . $fullpath]
                );

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
     * @param Disk $disk Disk scope.
     * @param string $path Destination location.
     * @return Response Result response.
     * @throws HttpException When requested path is invalid or is not a directory.
     * @throws NotFoundHttpException When requested path does not exist.
     * @version 0.1.1
     * @since 0.0.3
     */
    public function uploadAction(Disk $disk, $path = '')
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

        $request = $this->getRequest();

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

                $this->get('logger')->info(
                    \sprintf('File "%s" uploaded by user "%s".', $fullpath, $this->getUser()),
                    ['scope' => $disk->getSource()]
                );
                $this->addFlashMessage(
                    'done',
                    '"%file%" has been uploaded.',
                    ['%file%' => $disk . '/' . $fullpath]
                );

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
     * @param Disk $disk Disk scope.
     * @param string $path File being renamed.
     * @return Response Result response.
     * @throws HttpException When requested path is invalid.
     * @throws NotFoundHttpException When requested path does not exist.
     * @version 0.1.1
     * @since 0.0.3
     */
    public function renameAction(Disk $disk, $path)
    {
        $path = Controller::resolvePath($path);

        // get filesystem from given disk
        $filesystem = $disk->getFilesystem();
        $diskpath = $disk . '/' . $path;

        Controller::ensureExist($disk, $filesystem, $path);

        $request = $this->getRequest();

        // initialize form
        $form = $this->createForm(new RenameType($filesystem, $path), ['name' => null]);

        // only handle POST form submits
        if ($request->isMethod('POST')) {
            $form->bind($request);

            // validate form
            if ($form->isValid()) {
                $data = $form->getData();
                $basename = \basename($path);
                $dirpath = \dirname($path);
                $fullpath = $dirpath . '/' . $data['name'];

                $filesystem->move($path, $fullpath);

                $this->get('logger')->info(
                    \sprintf('File "%s" renamed to "%s" by user "%s".', $path, $data['name'], $this->getUser()),
                    ['scope' => $disk->getSource()]
                );
                $this->addFlashMessage(
                    'done',
                    '"%file%" has been renamed to "%name%".',
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
     * @param Disk $disk Disk scope.
     * @param string $path File being moved.
     * @param string $destination Destination location.
     * @return Response Result response.
     * @throws HttpException When requested path is invalid.
     * @throws NotFoundHttpException When requested path does not exist.
     * @version 0.1.1
     * @since 0.0.3
     */
    public function moveAction(Disk $disk, $path, $destination = '')
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

        $request = $this->getRequest();

        // only handle POST form submits
        if ($request->isMethod('POST')) {
            $name = \basename($path);
            $filesystem->move($path, $destination . '/' . $name);

            $this->get('logger')->info(
                \sprintf('File "%s" moved to "%s" by user "%s".', $path, $destination, $this->getUser()),
                ['scope' => $disk->getSource()]
            );
            $this->addFlashMessage(
                'done',
                '"%file%" has been moved to "%destination%".',
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
     * @param Disk $disk Disk scope.
     * @param string $path File being copied.
     * @param string $destination Destination location.
     * @return Response Result response.
     * @throws HttpException When requested path is invalid.
     * @throws NotFoundHttpException When requested path does not exist.
     * @version 0.1.1
     * @since 0.0.3
     */
    public function copyAction(Disk $disk, $path, $destination = '')
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

        $request = $this->getRequest();

        // only handle POST form submits
        if ($request->isMethod('POST')) {
            $name = \basename($path);
            $filesystem->copy($path, $destination . '/' . $name);

            $this->get('logger')->info(
                \sprintf('File "%s" copied to "%s" by user "%s".', $path, $destination, $this->getUser()),
                ['scope' => $disk->getSource()]
            );
            $this->addFlashMessage(
                'done',
                '"%file%" has been copied to "%destination%".',
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
}
