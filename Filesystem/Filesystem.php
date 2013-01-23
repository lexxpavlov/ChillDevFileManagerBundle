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
 * Abstract filesystem management.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.2
 * @since 0.0.2
 * @package ChillDev\Bundle\FileManagerBundle
 */
class Filesystem
{
    /**
     * I/O adapter.
     *
     * @var AdapterInterface
     * @version 0.0.2
     * @since 0.0.2
     */
    protected $adapter;

    /**
     * Initializes filesystem.
     *
     * @param AdapterInterface $adapter I/O adapter.
     * @version 0.0.2
     * @since 0.0.2
     */
    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Checks if file exists.
     *
     * @param string $path File path.
     * @return bool File state.
     * @version 0.0.2
     * @since 0.0.2
     */
    public function exists($path)
    {
        return $this->adapter->exists($path);
    }

    /**
     * Checks if given path is a regular file.
     *
     * @param string $path File path.
     * @return bool If it's file.
     * @version 0.0.2
     * @since 0.0.2
     */
    public function isFile($path)
    {
        return $this->adapter->isFile($path);
    }

    /**
     * Checks if given path is a directory.
     *
     * @param string $path File path.
     * @return bool If it's directory.
     * @version 0.0.2
     * @since 0.0.2
     */
    public function isDirectory($path)
    {
        return $this->adapter->isDirectory($path);
    }

    /**
     * Returns last modification file.
     *
     * @param string $path File path.
     * @return int Last modification time of file.
     * @version 0.0.2
     * @since 0.0.2
     */
    public function getFileMTime($path)
    {
        return $this->adapter->getFileMTime($path);
    }

    /**
     * Returns file size.
     *
     * @param string $path File path.
     * @return int Filesize.
     * @version 0.0.2
     * @since 0.0.2
     */
    public function getFilesize($path)
    {
        return $this->adapter->getFileSize($path);
    }

    /**
     * Removes regular file.
     *
     * @param string $path File path.
     * @version 0.0.2
     * @since 0.0.2
     */
    public function unlink($path)
    {
        $this->adapter->unlink($path);
    }

    /**
     * Prints file directly to output stream.
     *
     * @param string $path File path.
     * @version 0.0.2
     * @since 0.0.2
     */
    public function readFile($path)
    {
        $this->adapter->readFile($path);
    }

    /**
     * Creates new directory.
     *
     * @param string $path New directory path.
     * @version 0.0.2
     * @since 0.0.2
     */
    public function mkdir($path)
    {
        $this->adapter->mkdir($path);
    }

    /**
     * Initializes directory iterator.
     *
     * @param string $path Subject directory path.
     * @return DirectoryIterator Directory listing iterator.
     * @version 0.0.2
     * @since 0.0.2
     */
    public function createDirectoryIterator($path)
    {
        return new DirectoryIterator($this->adapter, $path);
    }
}
