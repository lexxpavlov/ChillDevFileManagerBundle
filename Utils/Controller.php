<?php

/**
 * This file is part of the ChillDev FileManager bundle.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2013 - 2014 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.3
 * @since 0.0.3
 * @package ChillDev\Bundle\FileManagerBundle
 */

namespace ChillDev\Bundle\FileManagerBundle\Utils;

use UnexpectedValueException;

use ChillDev\Bundle\FileManagerBundle\Filesystem\Disk;
use ChillDev\Bundle\FileManagerBundle\Filesystem\Filesystem;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller utilities.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2013 - 2014 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.3
 * @since 0.0.3
 * @package ChillDev\Bundle\FileManagerBundle
 */
class Controller
{
    /**
     * Resolve path and sanitizes it.
     *
     * @param string $path User-specified path.
     * @return string Normalized path.
     * @throws HttpException When specified path is invalid.
     * @version 0.0.3
     * @since 0.0.3
     */
    public static function resolvePath($path)
    {
        try {
            // resolve all symbolic references
            return Path::resolve($path);
        } catch (UnexpectedValueException $error) {
            // reference outside disk scope
            throw new HttpException(400, 'File path contains invalid reference that exceeds disk scope.', $error);
        }
    }

    /**
     * Ensure all specified paths exist.
     *
     * @param Disk $disk Disk reference.
     * @param Filesystem $filesystem Filesystem for I/O operations.
     * @param string $file,... Filepath to check.
     * @throws NotFoundHttpException When any of specified paths does not exist.
     * @version 0.0.3
     * @since 0.0.3
     */
    public static function ensureExist(Disk $disk, Filesystem $filesystem, $file/*unused*/)
    {
        foreach (\array_slice(\func_get_args(), 2) as $file) {
            if (!$filesystem->exists($file)) {
                // non-existing path
                throw new NotFoundHttpException(\sprintf('File "%s/%s" does not exist.', $disk, $file));
            }
        }
    }

    /**
     * Ensures that given path is (or is not) a directory.
     *
     * @param Disk $disk Disk reference.
     * @param Filesystem $filesystem Filesystem for I/O operations.
     * @param string $path User-specified path.
     * @param bool $flag Required directory flag.
     * @throws HttpException When specified path does not meet expected flag status.
     * @version 0.1.3
     * @since 0.1.3
     */
    public static function ensureDirectoryFlag(Disk $disk, Filesystem $filesystem, $path, $flag = true)
    {
        $info = $filesystem->getFileInfo($path);
        if ($flag xor $info->isDir()) {
            throw new HttpException(
                400,
                sprintf('"%s/%s" is' . ($flag ? ' not' : '') . ' a directory.', $disk, $path)
            );
        }
    }

    /**
     * Return sorting callback for sorting list of associative arrays.
     *
     * @param string $by Sorting field.
     * @param int $order Sorting order.
     * @return \Closure Sorting callback.
     * @version 0.0.3
     * @since 0.0.3
     */
    public static function getSorter($by, $order)
    {
        return function ($a, $b) use ($by, $order) {
            if (!isset($a[$by])) {
                return -$order;
            }

            if (!isset($b[$by])) {
                return $order;
            }

            return ($a[$by] > $b[$by] ? 1 : -1) * $order;
        };
    }
}
