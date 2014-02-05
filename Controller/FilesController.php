<?php

/**
 * This file is part of the ChillDev FileManager bundle.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012 - 2014 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.3
 * @since 0.0.2
 * @package ChillDev\Bundle\FileManagerBundle
 */

namespace ChillDev\Bundle\FileManagerBundle\Controller;

use DateTime;
use LogicException;

use ChillDev\Bundle\FileManagerBundle\Filesystem\Disk;
use ChillDev\Bundle\FileManagerBundle\Filesystem\Filesystem;
use ChillDev\Bundle\FileManagerBundle\Form\Type\MkdirType;
use ChillDev\Bundle\FileManagerBundle\Form\Type\RenameType;
use ChillDev\Bundle\FileManagerBundle\Form\Type\UploadType;
use ChillDev\Bundle\FileManagerBundle\Utils\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

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
 * @copyright 2012 - 2014 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.3
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
     * @version 0.1.3
     * @since 0.0.1
     */
    public function downloadAction(Request $request, Disk $disk, $path)
    {
        $path = Controller::resolvePath($path);

        // get filesystem from given disk
        $filesystem = $disk->getFilesystem();

        Controller::ensureExist($disk, $filesystem, $path);
        Controller::ensureDirectoryFlag($disk, $filesystem, $path, false);

        // file information object
        $info = $filesystem->getFileInfo($path);

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
     * @version 0.1.3
     * @since 0.0.1
     */
    public function mkdirAction(Request $request, Disk $disk, $path = '')
    {
        $path = Controller::resolvePath($path);

        // get filesystem from given disk
        $filesystem = $disk->getFilesystem();

        Controller::ensureExist($disk, $filesystem, $path);
        Controller::ensureDirectoryFlag($disk, $filesystem, $path);

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
     * @version 0.1.3
     * @since 0.0.3
     */
    public function uploadAction(Request $request, Disk $disk, $path = '')
    {
        $path = Controller::resolvePath($path);

        // get filesystem from given disk
        $filesystem = $disk->getFilesystem();

        Controller::ensureExist($disk, $filesystem, $path);
        Controller::ensureDirectoryFlag($disk, $filesystem, $path);

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
     * @version 0.1.3
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
        Controller::ensureDirectoryFlag($disk, $filesystem, $destination);

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

        return $this->renderDestinationDirectoryPicker(
            $destination,
            $disk,
            $filesystem,
            $path,
            $request->query->get('order', 1),
            'chilldev_filemanager_files_move',
            'Moving file %disk%/%path%'
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
     * @version 0.1.3
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
        Controller::ensureDirectoryFlag($disk, $filesystem, $destination);

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

        return $this->renderDestinationDirectoryPicker(
            $destination,
            $disk,
            $filesystem,
            $path,
            $request->query->get('order', 1),
            'chilldev_filemanager_files_copy',
            'Copying file %disk%/%path%'
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
        $this->logUserAction($disk, vsprintf($message, array_values($params)));

        // re-format pattern for named placeholders
        $message = \vsprintf($message . '.', \array_keys($params));

        $this->addFlashMessage('done', $message, $params);
    }

    /**
     * Generates page with destination location chooser for file operation.
     *
     * @param string $destination Current destination location.
     * @param Disk $disk Disk scope.
     * @param Filesystem $filesystem Filesystem handler.
     * @param string $path Source path.
     * @param int $order Order direction.
     * @param string $route Target route to use.
     * @param string $title List header.
     * @return Response Rendered destination chooser.
     * @version 0.1.2
     * @since 0.1.2
     */
    protected function renderDestinationDirectoryPicker(
        $destination,
        Disk $disk,
        Filesystem $filesystem,
        $path,
        $order,
        $route,
        $title
    ) {
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
        \uasort($list, Controller::getSorter('path', $order));

        // render destination choice view
        return $this->render(
            'ChillDevFileManagerBundle:Files:destination.html.default',
            [
                'disk' => $disk,
                'path' => $path,
                'destination' => $destination,
                'route' => $route,
                'title' => $title,
                'list' => $list,
            ]
        );
    }
}
