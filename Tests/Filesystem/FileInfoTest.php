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

use ChillDev\Bundle\FileManagerBundle\Filesystem\FileInfo;
use ChillDev\Bundle\FileManagerBundle\Tests\BaseAdapterTest;

/**
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.2
 * @since 0.0.2
 * @package ChillDev\Bundle\FileManagerBundle
 */
class FileInfoTest extends BaseAdapterTest
{
    /**
     * @test
     * @version 0.0.2
     * @since 0.0.2
     */
    public function isDirFromConstructor()
    {
        $toReturn = new \stdClass();

        $file = new FileInfo($this->getAdapterMock(), null, ['isDir' => $toReturn]);
        $this->assertSame($toReturn, $file->isDir(), 'FileInfo::isDir() should return pre-defined directory flag if specified.');
    }

    /**
     * @test
     * @version 0.0.2
     * @since 0.0.2
     */
    public function isDirFromAdapter()
    {
        $path = new \stdClass();

        $adapter = $this->getAdapterMock();
        $adapter->expects($this->once())
            ->method('isDirectory')
            ->with($this->identicalTo($path));

        $file = new FileInfo($adapter, $path);
        $file->isDir();
    }

    /**
     * @test
     * @version 0.0.2
     * @since 0.0.2
     */
    public function isFileFromConstructor()
    {
        $toReturn = new \stdClass();

        $file = new FileInfo($this->getAdapterMock(), null, ['isFile' => $toReturn]);
        $this->assertSame($toReturn, $file->isFile(), 'FileInfo::isFile() should return pre-defined file flag if specified.');
    }

    /**
     * @test
     * @version 0.0.2
     * @since 0.0.2
     */
    public function isFileFromAdapter()
    {
        $path = new \stdClass();

        $adapter = $this->getAdapterMock();
        $adapter->expects($this->once())
            ->method('isFile')
            ->with($this->identicalTo($path));

        $file = new FileInfo($adapter, $path);
        $file->isFile();
    }

    /**
     * @test
     * @version 0.0.2
     * @since 0.0.2
     */
    public function getSizeFromConstructor()
    {
        $toReturn = new \stdClass();

        $file = new FileInfo($this->getAdapterMock(), null, ['size' => $toReturn]);
        $this->assertSame($toReturn, $file->getSize(), 'FileInfo::getSize() should return pre-defined size if specified.');
    }

    /**
     * @test
     * @version 0.0.2
     * @since 0.0.2
     */
    public function getSizeFromAdapter()
    {
        $path = new \stdClass();

        $adapter = $this->getAdapterMock();
        $adapter->expects($this->once())
            ->method('getFilesize')
            ->with($this->identicalTo($path));

        $file = new FileInfo($adapter, $path);
        $file->getSize();
    }

    /**
     * @test
     * @version 0.0.2
     * @since 0.0.2
     */
    public function getMTimeFromConstructor()
    {
        $toReturn = new \stdClass();

        $file = new FileInfo($this->getAdapterMock(), null, ['mtime' => $toReturn]);
        $this->assertSame($toReturn, $file->getMTime(), 'FileInfo::getMTime() should return pre-defined mtime if specified.');
    }

    /**
     * @test
     * @version 0.0.2
     * @since 0.0.2
     */
    public function getMTimeFromAdapter()
    {
        $path = new \stdClass();

        $adapter = $this->getAdapterMock();
        $adapter->expects($this->once())
            ->method('getFileMTime')
            ->with($this->identicalTo($path));

        $file = new FileInfo($adapter, $path);
        $file->getMTime();
    }
}
