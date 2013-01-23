<?php

/**
 * This file is part of the ChillDev FileManager bundle.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012 - 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.2
 * @since 0.0.2
 * @package ChillDev\Bundle\FileManagerBundle
 */

namespace ChillDev\Bundle\FileManagerBundle\Controller;

use FilesystemIterator;
use UnexpectedValueException;

use ChillDev\Bundle\FileManagerBundle\Filesystem\Disk;
use ChillDev\Bundle\FileManagerBundle\Utils\Path;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Disks controller.
 *
 * @Route("/disks")
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.1
 * @since 0.0.1
 * @package ChillDev\Bundle\FileManagerBundle
 */
class DisksController extends Controller
{
    /**
     * Disks listing page.
     *
     * @Route("/", name="chilldev_filemanager_disks_list")
     * @Template(engine="config")
     * @return array Template data.
     * @version 0.0.1
     * @since 0.0.1
     */
    public function listAction()
    {
        return ['disks' => $this->get('chilldev.filemanager.disks.manager')];
    }

    /**
     * Directory listing action.
     *
     * @Route(
     *      "/{disk}/{path}",
     *      name="chilldev_filemanager_disks_browse",
     *      requirements={"path"=".*"},
     *      defaults={"path"=""}
     *  )
     * @Template(engine="config")
     * @param Disk $disk Disk scope.
     * @param string $path Destination directory.
     * @return array Template data.
     * @throws HttpException When requested path is invalid or is not a directory.
     * @throws NotFoundHttpException When requested path does not exist.
     * @version 0.0.2
     * @since 0.0.1
     */
    public function browseAction(Disk $disk, $path = '')
    {
        try {
            // resolve all symbolic references
            $path = Path::resolve($path);
        } catch (UnexpectedValueException $error) {
            // reference outside disk scope
            throw new HttpException(400, 'Directory path contains invalid reference that exceeds disk scope.', $error);
        }

        $list = [];

        // get filesystem from given disk
        $filesystem = $disk->getFilesystem();

        // non-existing path
        if (!$filesystem->exists($path)) {
            throw new NotFoundHttpException(\sprintf('Directory "%s/%s" does not exist.', $disk, $path));
        }

        if (!$filesystem->isDirectory($path)) {
            throw new HttpException(400, \sprintf('"%s/%s" is not a directory.', $disk, $path));
        }

        foreach ($filesystem->createDirectoryIterator($path) as $file => $info) {
            $data = [
                'isDirectory' => $info->isDir(),
                'path' => $path . '/' . $file,
            ];

            // directories doesn't have size
            if (!$info->isDir()) {
                $data['size'] = $info->getSize();
            }

            $list[$file] = $data;
        }

        return ['disk' => $disk, 'path' => $path, 'list' => $list];
    }
}
