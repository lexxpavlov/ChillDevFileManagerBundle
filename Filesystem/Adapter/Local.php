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

namespace ChillDev\Bundle\FileManagerBundle\Filesystem\Adapter;

use FilesystemIterator;

use ChillDev\Bundle\FileManagerBundle\Filesystem\AdapterInterface;

use Symfony\Component\Filesystem\Exception\IOException;

/**
 * Local filesystem implementation.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.2
 * @since 0.0.2
 * @package ChillDev\Bundle\FileManagerBundle
 */
class Local implements AdapterInterface
{
    /**
     * Root path for disk scope.
     *
     * @var string
     * @version 0.0.2
     * @since 0.0.2
     */
    protected $root;

    /**
     * Initializes adapter.
     *
     * @param string $root Root path for this disk.
     * @throws IOException When given path does not exist.
     * @version 0.0.2
     * @since 0.0.2
     */
    public function __construct($root)
    {
        if (!\file_exists($root)) {
            throw new IOException(\sprintf('Specified filesystem root path "%s" does not exist.', $root));
        }

        $this->root = $root;
    }

    /**
     * {@inheritDoc}
     * @version 0.0.2
     * @since 0.0.2
     */
    public function exists($path)
    {
        return \file_exists($this->root . $path);
    }

    /**
     * {@inheritDoc}
     * @version 0.0.2
     * @since 0.0.2
     */
    public function isFile($path)
    {
        return \is_file($this->root . $path);
    }

    /**
     * {@inheritDoc}
     * @version 0.0.2
     * @since 0.0.2
     */
    public function isDirectory($path)
    {
        return \is_dir($this->root . $path);
    }

    /**
     * {@inheritDoc}
     * @version 0.0.2
     * @since 0.0.2
     */
    public function getFileMTime($path)
    {
        return \filemtime($this->root . $path);
    }

    /**
     * {@inheritDoc}
     * @version 0.0.2
     * @since 0.0.2
     */
    public function getFilesize($path)
    {
        return \filesize($this->root . $path);
    }

    /**
     * {@inheritDoc}
     * @version 0.0.2
     * @since 0.0.2
     */
    public function unlink($path)
    {
        \unlink($this->root . $path);
    }

    /**
     * {@inheritDoc}
     * @version 0.0.2
     * @since 0.0.2
     */
    public function readFile($path)
    {
        \readfile($this->root . $path);
    }

    /**
     * {@inheritDoc}
     * @version 0.0.2
     * @since 0.0.2
     */
    public function mkdir($path)
    {
        \mkdir($this->root . $path);
    }

    /**
     * {@inheritDoc}
     * @version 0.0.2
     * @since 0.0.2
     */
    public function openDir($path)
    {
        return new FilesystemIterator(
            $this->root . $path,
            FilesystemIterator::KEY_AS_FILENAME
            | FilesystemIterator::CURRENT_AS_FILEINFO
            | FilesystemIterator::SKIP_DOTS
            | FilesystemIterator::UNIX_PATHS
        );
    }

    /**
     * {@inheritDoc}
     * @version 0.0.2
     * @since 0.0.2
     */
    public function closeDir($handle)
    {
        //dummy
    }

    /**
     * {@inheritDoc}
     * @version 0.0.2
     * @since 0.0.2
     */
    public function currentDir($handle)
    {
        $data = $handle->current();

        return [
            'path' => \preg_replace('#^' . \preg_quote($this->root, '#') . '#', '', $data->getPathname()),
            'size' => $data->getSize(),
            'isDir' => $data->isDir(),
        ];
    }

    /**
     * {@inheritDoc}
     * @version 0.0.2
     * @since 0.0.2
     */
    public function nextDir($handle)
    {
        $handle->next();
    }

    /**
     * {@inheritDoc}
     * @version 0.0.2
     * @since 0.0.2
     */
    public function keyDir($handle)
    {
        return $handle->key();
    }

    /**
     * {@inheritDoc}
     * @version 0.0.2
     * @since 0.0.2
     */
    public function validDir($handle)
    {
        return $handle->valid();
    }

    /**
     * {@inheritDoc}
     * @version 0.0.2
     * @since 0.0.2
     */
    public function rewindDir($handle)
    {
        $handle->rewind();
    }
}
