<?php

/**
 * This file is part of the ChillDev FileManager bundle.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.2
 * @since 0.0.2
 * @package ChillDev\Bundle\FileManagerBundle
 */

namespace ChillDev\Bundle\FileManagerBundle\Filesystem;

/**
 * Filesystem I/O interface.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.2
 * @since 0.0.2
 * @package ChillDev\Bundle\FileManagerBundle
 */
interface AdapterInterface
{
    /**
     * Checks if file exists.
     *
     * @param string $path File path.
     * @return bool File state.
     * @version 0.0.2
     * @since 0.0.2
     */
    public function exists($path);

    /**
     * Checks if given path is a regular file.
     *
     * @param string $path File path.
     * @return bool If it's file.
     * @version 0.0.2
     * @since 0.0.2
     */
    public function isFile($path);

    /**
     * Checks if given path is a directory.
     *
     * @param string $path File path.
     * @return bool If it's directory.
     * @version 0.0.2
     * @since 0.0.2
     */
    public function isDirectory($path);

    /**
     * Returns last modification file.
     *
     * @param string $path File path.
     * @return int Last modification time of file.
     * @version 0.0.2
     * @since 0.0.2
     */
    public function getFileMTime($path);

    /**
     * Returns file size.
     *
     * @param string $path File path.
     * @return int Filesize.
     * @version 0.0.2
     * @since 0.0.2
     */
    public function getFilesize($path);

    /**
     * Removes regular file.
     *
     * @param string $path File path.
     * @version 0.0.2
     * @since 0.0.2
     */
    public function unlink($path);

    /**
     * Prints file directly to output stream.
     *
     * @param string $path File path.
     * @version 0.0.2
     * @since 0.0.2
     */
    public function readFile($path);

    /**
     * Creates new directory.
     *
     * @param string $path New directory path.
     * @version 0.0.2
     * @since 0.0.2
     */
    public function mkdir($path);

    /**
     * Creates directory iterator.
     *
     * @param string $path Directory path.
     * @return mixed Iterator handle.
     * @version 0.0.2
     * @since 0.0.2
     */
    public function openDir($path);

    /**
     * Closes directory iterator.
     *
     * @param mixed $handle Iterator handle.
     * @version 0.0.2
     * @since 0.0.2
     */
    public function closeDir($handle);

    /**
     * Returns current element.
     *
     * @param mixed $handle Iterator handle.
     * @return array File data.
     * @version 0.0.2
     * @since 0.0.2
     */
    public function currentDir($handle);

    /**
     * Advances iterator.
     *
     * @param mixed $handle Iterator handle.
     * @version 0.0.2
     * @since 0.0.2
     */
    public function nextDir($handle);

    /**
     * Returns current iterator index.
     *
     * @param mixed $handle Iterator handle.
     * @return string Filename.
     * @version 0.0.2
     * @since 0.0.2
     */
    public function keyDir($handle);

    /**
     * Checks if directory iterator has more items.
     *
     * @param mixed $handle Iterator handle.
     * @return bool Iterator status.
     * @version 0.0.2
     * @since 0.0.2
     */
    public function validDir($handle);

    /**
     * Rewinds directory iterator.
     *
     * @param mixed $handle Iterator handle.
     * @version 0.0.2
     * @since 0.0.2
     */
    public function rewindDir($handle);
}
