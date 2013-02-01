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

namespace ChillDev\Bundle\FileManagerBundle\Tests\Filesystem\Adapter;

use PHPUnit_Framework_TestCase;

use ChillDev\Bundle\FileManagerBundle\Filesystem\Adapter\Local;

use org\bovigo\vfs\vfsStream;

/**
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.2
 * @since 0.0.2
 * @package ChillDev\Bundle\FileManagerBundle
 */
class LocalTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     * @version 0.0.2
     * @since 0.0.2
     */
    const ROOT_DIR = 'root';

    /**
     * @test
     * @expectedException Symfony\Component\Filesystem\Exception\IOException
     * @expectedExceptionMessage Specified filesystem root path "test" does not exist.
     * @version 0.0.2
     * @since 0.0.2
     */
    public function constructorWithNonexistingPath()
    {
        new Local('test');
    }

    /**
     * @test
     * @version 0.0.2
     * @since 0.0.2
     */
    public function exists()
    {
        self::setupVfs(['foo' => '']);

        $adapter = $this->getAdapter();

        $this->assertTrue($adapter->exists('foo'), 'Local::exists() should return TRUE for files that exist.');
        $this->assertFalse($adapter->exists('test'), 'Local::exists() should return FALSE for files that don\'t exist.');
    }

    /**
     * @test
     * @version 0.0.2
     * @since 0.0.2
     */
    public function isFile()
    {
        self::setupVfs(['foo' => '', 'bar' => []]);

        $adapter = $this->getAdapter();

        $this->assertTrue($adapter->isFile('foo'), 'Local::isFile() should return TRUE for regular files.');
        $this->assertFalse($adapter->isFile('bar'), 'Local::isFile() should return FALSE for other files.');
    }

    /**
     * @test
     * @version 0.0.2
     * @since 0.0.2
     */
    public function isDirectory()
    {
        self::setupVfs(['foo' => '', 'bar' => []]);

        $adapter = $this->getAdapter();

        $this->assertTrue($adapter->isDirectory('bar'), 'Local::isDirectory() should return TRUE for directories.');
        $this->assertFalse($adapter->isDirectory('foo'), 'Local::isDirectory() should return FALSE for other files.');
    }

    /**
     * @test
     * @version 0.0.2
     * @since 0.0.2
     */
    public function getFileMTime()
    {
        $filename = 'foo';

        self::setupVfs([$filename => 'foo']);

        $this->assertEquals(\filemtime($this->getRootPath() . $filename), $this->getAdapter()->getFileMTime($filename), 'Local::getFileMTime() should return last modification time of file.');
    }

    /**
     * @test
     * @version 0.0.2
     * @since 0.0.2
     */
    public function getFilesize()
    {
        $filename = 'foo';

        self::setupVfs([$filename => 'foo']);

        $this->assertEquals(\filesize($this->getRootPath() . $filename), $this->getAdapter()->getFilesize($filename), 'Local::getFilesize() should return filesize of file.');
    }

    /**
     * @test
     * @version 0.0.2
     * @since 0.0.2
     */
    public function unlink()
    {
        $filename = 'foo';

        self::setupVfs([$filename => '']);

        $realpath = $this->getRootPath() . $filename;

        $this->getAdapter()->unlink($filename);
        $this->assertFileNotExists($realpath, 'Local::unlink() should delete the file.');
    }

    /**
     * @test
     * @version 0.0.2
     * @since 0.0.2
     */
    public function readFile()
    {
        $filename = 'foo';
        $content = 'bar';

        self::setupVfs([$filename => $content]);

        $this->expectOutputString($content);
        $this->getAdapter()->readFile($filename);
    }

    /**
     * @test
     * @version 0.0.2
     * @since 0.0.2
     */
    public function mkdir()
    {
        $filename = 'foo';

        vfsStream::setup(self::ROOT_DIR);

        $this->getAdapter()->mkdir($filename);

        $realpath = $this->getRootPath() . $filename;

        $this->assertFileExists($realpath, 'Local::mkdir() should create new directory.');
        $this->assertTrue(\is_dir($realpath), 'Local::mkdir() should create new directory.');

        \rmdir($realpath);
    }

    /**
     * @test
     * @version 0.0.2
     * @since 0.0.2
     */
    public function openDir()
    {
        $filename = 'foo';

        self::setupVfs([$filename => []]);

        $adapter = $this->getAdapter();

        $handle = $adapter->openDir($filename);

        $this->assertInstanceOf('DirectoryIterator', $handle, 'Local::openDir() should create directory iterator for given path.');

        // this is just to increate code covrage
        $adapter->closeDir($handle);
    }

    /**
     * @test
     * @version 0.0.2
     * @since 0.0.2
     */
    public function currentDir()
    {
        $path = 'foo';
        $size = new \stdClass();
        $isDir = new \stdClass();

        $info = $this->getMock('SplFileInfo', [], [], '', false);
        $info->expects($this->once())
            ->method('getPathname')
            ->will($this->returnValue($this->getRootPath() . $path));
        $info->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue($size));
        $info->expects($this->once())
            ->method('isDir')
            ->will($this->returnValue($isDir));

        $handle = $this->getMock('DirectoryIterator', [], [], '', false);
        $handle->expects($this->once())
            ->method('current')
            ->will($this->returnValue($info));

        $result = $this->getAdapter()->currentDir($handle);

        $this->assertEquals($path, $result['path'], 'Local::currentDir() should return file path under "path" key.');
        $this->assertSame($size, $result['size'], 'Local::currentDir() should return file path under "size" key.');
        $this->assertSame($isDir, $result['isDir'], 'Local::currentDir() should return file path under "isDir" key.');
    }

    /**
     * @test
     * @version 0.0.2
     * @since 0.0.2
     */
    public function nextDir()
    {
        $handle = $this->getMock('DirectoryIterator', [], [], '', false);
        $handle->expects($this->once())
            ->method('next');

        $this->getAdapter()->nextDir($handle);
    }

    /**
     * @test
     * @version 0.0.2
     * @since 0.0.2
     */
    public function keyDir()
    {
        $toReturn = new \stdClass();

        $handle = $this->getMock('DirectoryIterator', [], [], '', false);
        $handle->expects($this->once())
            ->method('key')
            ->will($this->returnValue($toReturn));

        $result = $this->getAdapter()->keyDir($handle);
        $this->assertSame($toReturn, $result, 'Local::keyDir() should return result of sub-sequent $handle::key() call.');
    }

    /**
     * @test
     * @version 0.0.2
     * @since 0.0.2
     */
    public function validDir()
    {
        $toReturn = new \stdClass();

        $handle = $this->getMock('DirectoryIterator', [], [], '', false);
        $handle->expects($this->once())
            ->method('valid')
            ->will($this->returnValue($toReturn));

        $result = $this->getAdapter()->validDir($handle);
        $this->assertSame($toReturn, $result, 'Local::validDir() should return result of sub-sequent $handle::valid() call.');
    }

    /**
     * @test
     * @version 0.0.2
     * @since 0.0.2
     */
    public function rewindDir()
    {
        $handle = $this->getMock('DirectoryIterator', [], [], '', false);
        $handle->expects($this->once())
            ->method('rewind');

        $this->getAdapter()->rewindDir($handle);
    }

    /**
     * @return Local
     * @version 0.0.2
     * @since 0.0.2
     */
    protected function getAdapter()
    {
        return new Local($this->getRootPath());
    }

    /**
     * @return string
     * @version 0.0.2
     * @since 0.0.2
     */
    protected function getRootPath()
    {
        return vfsStream::url(self::ROOT_DIR) . '/';
    }

    /**
     * @param array $structure
     * @version 0.0.2
     * @since 0.0.2
     */
    protected static function setupVfs(array $structure)
    {
        vfsStream::setup(self::ROOT_DIR, null, $structure);
    }
}
