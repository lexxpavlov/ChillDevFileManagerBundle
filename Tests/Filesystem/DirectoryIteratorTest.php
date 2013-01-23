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

use ChillDev\Bundle\FileManagerBundle\Filesystem\DirectoryIterator;
use ChillDev\Bundle\FileManagerBundle\Tests\BaseAdapterTest;

/**
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.2
 * @since 0.0.2
 * @package ChillDev\Bundle\FileManagerBundle
 */
class DirectoryIteratorTest extends BaseAdapterTest
{
    /**
     * @test
     * @version 0.0.2
     * @since 0.0.2
     */
    public function constructorAndDestructor()
    {
        $path = new \stdClass();
        $handle = new \stdClass();

        $adapter = $this->getAdapterMock();
        $adapter->expects($this->once())
            ->method('openDir')
            ->with($this->identicalTo($path))
            ->will($this->returnValue($handle));
        $adapter->expects($this->once())
            ->method('closeDir')
            ->with($this->identicalTo($handle));

        new DirectoryIterator($adapter, $path);
    }

    /**
     * @test
     * @version 0.0.2
     * @since 0.0.2
     */
    public function currentWithHandle()
    {
        $handle = new \stdClass();
        $toReturn = new \stdClass();

        $adapter = $this->getAdapterMock();
        $adapter->expects($this->once())
            ->method('openDir')
            ->with($this->anything())
            ->will($this->returnValue($handle));
        $adapter->expects($this->once())
            ->method('currentDir')
            ->with($this->identicalTo($handle))
            ->will($this->returnValue([
                'path' => null,
                'size' => $toReturn,
            ]));

        $file = (new DirectoryIterator($adapter, null))->current();
        $this->assertSame($toReturn, $file->getSize(), 'DirectoryIterator::current() should return result of $adapter::currentDir() with pre-defined flags.');
    }

    /**
     * @test
     * @version 0.0.2
     * @since 0.0.2
     */
    public function nextWithHandle()
    {
        $handle = new \stdClass();

        $adapter = $this->getAdapterMock();
        $adapter->expects($this->once())
            ->method('openDir')
            ->with($this->anything())
            ->will($this->returnValue($handle));
        $adapter->expects($this->once())
            ->method('nextDir')
            ->with($this->identicalTo($handle));

        (new DirectoryIterator($adapter, null))->next();
    }

    /**
     * @test
     * @version 0.0.2
     * @since 0.0.2
     */
    public function keyWithHandle()
    {
        $handle = new \stdClass();
        $toReturn = new \stdClass();

        $adapter = $this->getAdapterMock();
        $adapter->expects($this->once())
            ->method('openDir')
            ->with($this->anything())
            ->will($this->returnValue($handle));
        $adapter->expects($this->once())
            ->method('keyDir')
            ->with($this->identicalTo($handle))
            ->will($this->returnValue($toReturn));

        $result = (new DirectoryIterator($adapter, null))->key();
        $this->assertSame($toReturn, $result, 'DirectoryIterator::key() should return result of $adapter::keyDir().');
    }

    /**
     * @test
     * @version 0.0.2
     * @since 0.0.2
     */
    public function validWithHandle()
    {
        $handle = new \stdClass();
        $toReturn = new \stdClass();

        $adapter = $this->getAdapterMock();
        $adapter->expects($this->once())
            ->method('openDir')
            ->with($this->anything())
            ->will($this->returnValue($handle));
        $adapter->expects($this->once())
            ->method('validDir')
            ->with($this->identicalTo($handle))
            ->will($this->returnValue($toReturn));

        $result = (new DirectoryIterator($adapter, null))->valid();
        $this->assertSame($toReturn, $result, 'DirectoryIterator::valid() should return result of $adapter::validDir().');
    }

    /**
     * @test
     * @version 0.0.2
     * @since 0.0.2
     */
    public function rewindWithHandle()
    {
        $handle = new \stdClass();

        $adapter = $this->getAdapterMock();
        $adapter->expects($this->once())
            ->method('openDir')
            ->with($this->anything())
            ->will($this->returnValue($handle));
        $adapter->expects($this->once())
            ->method('rewindDir')
            ->with($this->identicalTo($handle));

        (new DirectoryIterator($adapter, null))->rewind();
    }
}
