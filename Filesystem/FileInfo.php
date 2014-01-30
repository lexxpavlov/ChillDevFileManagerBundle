<?php

/**
 * This file is part of the ChillDev FileManager bundle.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2014 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.3
 * @since 0.1.3
 * @package ChillDev\Bundle\FileManagerBundle
 */

namespace ChillDev\Bundle\FileManagerBundle\Filesystem;

use finfo;
use SplFileInfo;

/**
 * Extended file info class.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2014 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.3
 * @since 0.1.3
 * @package ChillDev\Bundle\FileManagerBundle
 */
class FileInfo extends SplFileInfo
{
    /**
     * Directory MIME type label.
     *
     * @var stringfinfo
     * @version 0.1.3
     * @since 0.1.3
     */
    const DIRECTORY = 'directory';

    /**
     * finfo library resource instance.
     *
     * @var finfo
     * @version 0.1.3
     * @since 0.1.3
     */
    protected static $finfo;

    /**
     * Initializes (if needed) and returns finfo resource.
     *
     * @var finfo
     * @version 0.1.3
     * @since 0.1.3
     */
    protected static function getFinfo()
    {
        if (!isset(static::$finfo)) {
            static::$finfo = new finfo(\FILEINFO_MIME_TYPE);
        }

        return static::$finfo;
    }

    /**
     * Returns file MIME type.
     *
     * @return string File MIME type.
     * @version 0.1.3
     * @since 0.1.3
     */
    public function getMimeType()
    {
        // fix for some stream wrappers
        if ($this->isDir()) {
            return static::DIRECTORY;
        }

        return static::getFinfo()->file($this->getPathname());
    }
}
