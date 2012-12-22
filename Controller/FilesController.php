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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
     *      "/{disk}/{path}",
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
        $path = '/' . $path;

        // resolve all symbolic references
        $path = \preg_replace('#//+#', '/', $path);
        while (\preg_match('#/([^/]+/\\.)?\\./#', $path, $match, \PREG_OFFSET_CAPTURE) > 0) {
            $path = \substr_replace($path, '/', $match[0][1], \strlen($match[0][0]));
        }

        // reference outside disk scope
        if (\strpos($path, '/../') !== false) {
            throw new HttpException(400, 'File path contains invalid reference that exceeds disk scope.');
        }

        $path = \substr($path, 1);

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
}
