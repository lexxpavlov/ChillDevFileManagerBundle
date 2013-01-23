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
 * Single path information representation.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.2
 * @since 0.0.2
 * @package ChillDev\Bundle\FileManagerBundle
 */
class FileInfo
{
    /**
     * Filesystem adapter.
     *
     * @var AdapterInterface
     * @version 0.0.2
     * @since 0.0.2
     */
    protected $adapter;

    /**
     * File path.
     *
     * @var string
     * @version 0.0.2
     * @since 0.0.2
     */
    protected $path;

    /**
     * Pre-defined data.
     *
     * @var array
     * @version 0.0.2
     * @since 0.0.2
     */
    protected $handle;

    /**
     * Initializes file info object.
     *
     * @param AdapterInterface $adapter Filesystem I/O.
     * @param string $path File path.
     * @param array $data Pre-defined data.
     * @version 0.0.2
     * @since 0.0.2
     */
    public function __construct(AdapterInterface $adapter, $path, array $data = [])
    {
        $this->adapter = $adapter;
        $this->path = $path;
        $this->data = $data;
    }

    /**
     * Checks if file is a directory.
     *
     * @return bool Test result.
     * @version 0.0.2
     * @since 0.0.2
     */
    public function isDir()
    {
        return isset($this->data['isDir']) ? $this->data['isDir'] : $this->adapter->isDirectory($this->path);
    }

    /**
     * Checks if file is a regular file.
     *
     * @return bool Test result.
     * @version 0.0.2
     * @since 0.0.2
     */
    public function isFile()
    {
        return isset($this->data['isFile']) ? $this->data['isFile'] : $this->adapter->isFile($this->path);
    }

    /**
     * Returns filesize.
     *
     * @return int Filesize.
     * @version 0.0.2
     * @since 0.0.2
     */
    public function getSize()
    {
        return isset($this->data['size']) ? $this->data['size'] : $this->adapter->getFilesize($this->path);
    }

    /**
     * Returns file last modification time.
     *
     * @return int Last modification time of file.
     * @version 0.0.2
     * @since 0.0.2
     */
    public function getMTime()
    {
        return isset($this->data['mtime']) ? $this->data['mtime'] : $this->adapter->getFileMTime($this->path);
    }
}
