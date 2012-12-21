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

use FilesystemIterator;

use ChillDev\Bundle\FileManagerBundle\Filesystem\Disk;

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
     * @version 0.0.1
     * @since 0.0.1
     */
    public function browseAction(Disk $disk, $path = '')
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

        $path = \substr($path, 1);
        $list = [];

        // list directory content - very primitive way for now, needs abstraction in future
        $realpath = \realpath($disk->getSource() . $path);
        $directory = new FilesystemIterator(
            $realpath,
            FilesystemIterator::KEY_AS_FILENAME
            | FilesystemIterator::CURRENT_AS_FILEINFO
            | FilesystemIterator::SKIP_DOTS
            | FilesystemIterator::UNIX_PATHS
        );
        foreach ($directory as $file => $info) {
            $data = [
                'isDirectory' => $info->isDir(),
                'path' => $path . $file,
            ];

            // directories doesn't have size
            if (!$info->isDir()) {
                $data['size'] = $info->getSize();
            }

            $list[$file] = $data;
        }

        return ['disk' => $disk, 'path' => \substr($path, 0, -1), 'list' => $list];
    }
}
