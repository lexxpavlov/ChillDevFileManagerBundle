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

use Iterator;

/**
 * Directory listing iterator.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.2
 * @since 0.0.2
 * @package ChillDev\Bundle\FileManagerBundle
 */
class DirectoryIterator implements Iterator
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
     * I/O iterator handle.
     *
     * @var mixed
     * @version 0.0.2
     * @since 0.0.2
     */
    protected $handle;

    /**
     * Initializes iterator.
     *
     * @param AdapterInterface $adapter Filesystem I/O.
     * @param string $path Subject directory path.
     * @version 0.0.2
     * @since 0.0.2
     */
    public function __construct(AdapterInterface $adapter, $path)
    {
        $this->adapter = $adapter;
        $this->handle = $this->adapter->openDir($path);
    }

    /**
     * Closes iterator handle.
     *
     * @version 0.0.2
     * @since 0.0.2
     */
    public function __destruct()
    {
        $this->adapter->closeDir($this->handle);
    }

    /**
     * Returns current element.
     *
     * @return FileInfo File information.
     * @version 0.0.2
     * @since 0.0.2
     */
    public function current()
    {
        $data = $this->adapter->currentDir($this->handle);

        // construct file data instance
        return new FileInfo($this->adapter, $data['path'], $data);
    }

    /**
     * Advances iterator.
     *
     * @version 0.0.2
     * @since 0.0.2
     */
    public function next()
    {
        $this->adapter->nextDir($this->handle);
    }

    /**
     * Returns current iterator index.
     *
     * @return string Filename.
     * @version 0.0.2
     * @since 0.0.2
     */
    public function key()
    {
        return $this->adapter->keyDir($this->handle);
    }

    /**
     * Checks if directory iterator has more items.
     *
     * @return bool Iterator status.
     * @version 0.0.2
     * @since 0.0.2
     */
    public function valid()
    {
        return $this->adapter->validDir($this->handle);
    }

    /**
     * Rewinds directory iterator.
     *
     * @version 0.0.2
     * @since 0.0.2
     */
    public function rewind()
    {
        $this->adapter->rewindDir($this->handle);
    }
}
