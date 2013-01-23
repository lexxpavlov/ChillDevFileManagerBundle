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

namespace ChillDev\Bundle\FileManagerBundle\Tests\Filesystem;

use ChillDev\Bundle\FileManagerBundle\Filesystem\Filesystem;
use ChillDev\Bundle\FileManagerBundle\Tests\BaseAdapterTest;

/**
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.2
 * @since 0.0.2
 * @package ChillDev\Bundle\FileManagerBundle
 */
class FilesystemTest extends BaseAdapterTest
{
    /**
     * @test
     * @version 0.0.2
     * @since 0.0.2
     */
    public function existsFromAdapter()
    {
        $path = new \stdClass();
        $toReturn = new \stdClass();

        $adapter = $this->getAdapterMock();
        $adapter->expects($this->once())
            ->method('exists')
            ->with($this->identicalTo($path))
            ->will($this->returnValue($toReturn));

        $filesystem = new Filesystem($adapter);
        $result = $filesystem->exists($path);
        $this->assertSame($toReturn, $result, 'Filesystem::exists() should return result of underlying $adapter::exists() call.');
    }

    /**
     * @test
     * @version 0.0.2
     * @since 0.0.2
     */
    public function isFileFromAdapter()
    {
        $path = new \stdClass();
        $toReturn = new \stdClass();

        $adapter = $this->getAdapterMock();
        $adapter->expects($this->once())
            ->method('isFile')
            ->with($this->identicalTo($path))
            ->will($this->returnValue($toReturn));

        $filesystem = new Filesystem($adapter);
        $result = $filesystem->isFile($path);
        $this->assertSame($toReturn, $result, 'Filesystem::isFile() should return result of underlying $adapter::isFile() call.');
    }

    /**
     * @test
     * @version 0.0.2
     * @since 0.0.2
     */
    public function isDirectoryFromAdapter()
    {
        $path = new \stdClass();
        $toReturn = new \stdClass();

        $adapter = $this->getAdapterMock();
        $adapter->expects($this->once())
            ->method('isDirectory')
            ->with($this->identicalTo($path))
            ->will($this->returnValue($toReturn));

        $filesystem = new Filesystem($adapter);
        $result = $filesystem->isDirectory($path);
        $this->assertSame($toReturn, $result, 'Filesystem::isDirectory() should return result of underlying $adapter::isDirectory() call.');
    }

    /**
     * @test
     * @version 0.0.2
     * @since 0.0.2
     */
    public function getFileMTimeFromAdapter()
    {
        $path = new \stdClass();
        $toReturn = new \stdClass();

        $adapter = $this->getAdapterMock();
        $adapter->expects($this->once())
            ->method('getFileMTime')
            ->with($this->identicalTo($path))
            ->will($this->returnValue($toReturn));

        $filesystem = new Filesystem($adapter);
        $result = $filesystem->getFileMTime($path);
        $this->assertSame($toReturn, $result, 'Filesystem::getFileMTime() should return result of underlying $adapter::getFileMTime() call.');
    }

    /**
     * @test
     * @version 0.0.2
     * @since 0.0.2
     */
    public function getFilesizeFromAdapter()
    {
        $path = new \stdClass();
        $toReturn = new \stdClass();

        $adapter = $this->getAdapterMock();
        $adapter->expects($this->once())
            ->method('getFilesize')
            ->with($this->identicalTo($path))
            ->will($this->returnValue($toReturn));

        $filesystem = new Filesystem($adapter);
        $result = $filesystem->getFilesize($path);
        $this->assertSame($toReturn, $result, 'Filesystem::getFilesize() should return result of underlying $adapter::getFilesize() call.');
    }

    /**
     * @test
     * @version 0.0.2
     * @since 0.0.2
     */
    public function unlinkFromAdapter()
    {
        $path = new \stdClass();

        $adapter = $this->getAdapterMock();
        $adapter->expects($this->once())
            ->method('unlink')
            ->with($this->identicalTo($path));

        $filesystem = new Filesystem($adapter);
        $filesystem->unlink($path);
    }

    /**
     * @test
     * @version 0.0.2
     * @since 0.0.2
     */
    public function readFileFromAdapter()
    {
        $path = new \stdClass();

        $adapter = $this->getAdapterMock();
        $adapter->expects($this->once())
            ->method('readFile')
            ->with($this->identicalTo($path));

        $filesystem = new Filesystem($adapter);
        $filesystem->readFile($path);
    }

    /**
     * @test
     * @version 0.0.2
     * @since 0.0.2
     */
    public function mkdirFromAdapter()
    {
        $path = new \stdClass();

        $adapter = $this->getAdapterMock();
        $adapter->expects($this->once())
            ->method('mkdir')
            ->with($this->identicalTo($path));

        $filesystem = new Filesystem($adapter);
        $filesystem->mkdir($path);
    }

    /**
     * @test
     * @version 0.0.2
     * @since 0.0.2
     */
    public function createDirectoryIteratorWithAdapter()
    {
        $path = new \stdClass();

        $adapter = $this->getAdapterMock();
        $adapter->expects($this->once())
            ->method('openDir')
            ->with($this->identicalTo($path));

        $filesystem = new Filesystem($adapter);
        $result = $filesystem->createDirectoryIterator($path);
        $this->assertInstanceOf('ChillDev\\Bundle\\FileManagerBundle\\Filesystem\\DirectoryIterator', $result, 'Filesystem::createDirectoryIterator() should return instance of DirectoryIterator class.');
    }
}
