<?php

/**
 * This file is part of the ChillDev FileManager bundle.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.3
 * @since 0.0.2
 * @package ChillDev\Bundle\FileManagerBundle
 */

namespace ChillDev\Bundle\FileManagerBundle\Tests\Filesystem;

use FilesystemIterator;

use PHPUnit_Framework_TestCase;

use ChillDev\Bundle\FileManagerBundle\Filesystem\Filesystem;

use org\bovigo\vfs\vfsStream;

/**
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.3
 * @since 0.0.2
 * @package ChillDev\Bundle\FileManagerBundle
 */
class FilesystemTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     * @version 0.0.3
     * @since 0.0.3
     */
    const ROOT_DIR = 'root';

    /**
     * @test
     * @expectedException Symfony\Component\Filesystem\Exception\IOException
     * @expectedExceptionMessage Specified filesystem root path "test" does not exist.
     * @version 0.0.3
     * @since 0.0.3
     */
    public function constructorWithNonexistingPath()
    {
        new Filesystem('test');
    }

    /**
     * @test
     * @version 0.0.3
     * @since 0.0.3
     */
    public function exists()
    {
        self::setupVfs(['foo' => '']);

        $filesystem = $this->getFilesystem();

        $this->assertTrue($filesystem->exists('foo'), 'Filesystem::exists() should return TRUE for files that exist.');
        $this->assertFalse($filesystem->exists('test'), 'Filesystem::exists() should return FALSE for files that don\'t exist.');
    }

    /**
     * @test
     * @version 0.0.3
     * @since 0.0.3
     */
    public function remove()
    {
        $filename = 'foo';

        self::setupVfs([$filename => '']);

        $realpath = $this->getRootPath() . $filename;

        $this->getFilesystem()->remove($filename);
        $this->assertFileNotExists($realpath, 'Filesystem::remove() should delete the file.');
    }

    /**
     * @test
     * @version 0.0.3
     * @since 0.0.3
     */
    public function move()
    {
        $filename1 = 'foo';
        $filename2 = 'bar';
        $directory = 'baz';

        self::setupVfs([$filename1 => '', $directory => []]);

        $realpath1 = $this->getRootPath() . $filename1;
        $realpath2 = $this->getRootPath() . $directory . '/' . $filename2;

        $this->getFilesystem()->move($filename1, $directory . '/' . $filename2);
        $this->assertFileNotExists($realpath1, 'Filesystem::move() should move file from old location.');
        $this->assertFileExists($realpath2, 'Filesystem::move() should move file to new location.');
    }

    /**
     * @test
     * @version 0.0.3
     * @since 0.0.3
     */
    public function mkdir()
    {
        $filename = 'foo';

        vfsStream::setup(self::ROOT_DIR);

        $this->getFilesystem()->mkdir($filename);

        $realpath = $this->getRootPath() . $filename;

        $this->assertFileExists($realpath, 'Filesystem::mkdir() should create new directory.');
        $this->assertTrue(\is_dir($realpath), 'Filesystem::mkdir() should create new directory.');
    }

    /**
     * @test
     * @version 0.0.3
     * @since 0.0.3
     */
    public function upload()
    {
        $path = 'foo';
        $filename = 'bar';

        vfsStream::setup(self::ROOT_DIR);

        $file = $this->getMock('Symfony\\Component\\HttpFoundation\\File\\UploadedFile', [], [], '', false);
        $file->expects($this->once())
            ->method('move')
            ->with($this->equalTo($this->getRootPath() . $path), $this->equalTo($filename));

        $this->getFilesystem()->upload($path, $file, $filename);
    }

    /**
     * @test
     * @version 0.0.3
     * @since 0.0.3
     */
    public function createDirectoryIterator()
    {
        $filename = 'foo';

        self::setupVfs([$filename => []]);

        $iterator = $this->getFilesystem()->createDirectoryIterator($filename);

        $this->assertInstanceOf('FilesystemIterator', $iterator, 'Filesystem::createDirectoryIterator() should return instance of FilesystemIterator class.');
        $this->assertEquals($this->getRootPath() . $filename, $iterator->getPath(), 'Filesystem::createDirectoryIterator() should create FilesystemIterator for specified path.');

        $flags = $iterator->getFlags();

        $this->assertEquals(FilesystemIterator::KEY_AS_FILENAME, $flags & FilesystemIterator::KEY_AS_FILENAME, 'Filesystem::createDirectoryIterator() should create FilesystemIterator with KEY_AS_FILENAME flag set.');
        $this->assertEquals(FilesystemIterator::CURRENT_AS_FILEINFO, $flags & FilesystemIterator::CURRENT_AS_FILEINFO, 'Filesystem::createDirectoryIterator() should create FilesystemIterator with CURRENT_AS_FILEINFO flag set.');
        $this->assertEquals(FilesystemIterator::SKIP_DOTS, $flags & FilesystemIterator::SKIP_DOTS, 'Filesystem::createDirectoryIterator() should create FilesystemIterator with SKIP_DOTS flag set.');
        $this->assertEquals(FilesystemIterator::UNIX_PATHS, $flags & FilesystemIterator::UNIX_PATHS, 'Filesystem::createDirectoryIterator() should create FilesystemIterator with UNIX_PATHS flag set.');
    }

    /**
     * @test
     * @version 0.0.3
     * @since 0.0.3
     */
    public function getFileInfo()
    {
        $filename = 'foo';

        self::setupVfs([$filename => '']);

        $info = $this->getFilesystem()->getFileInfo($filename);

        $this->assertInstanceOf('SplFileInfo', $info, 'Filesystem::getFileInfo() should return instance of SplFileInfo class.');
        $this->assertEquals($this->getRootPath() . $filename, $info->getPathname(), 'Filesystem::getFileInfo() should create SplFileInfo for specified path');
    }

    /**
     * @return Filesystem
     * @version 0.0.3
     * @since 0.0.3
     */
    protected function getFilesystem()
    {
        return new Filesystem($this->getRootPath());
    }

    /**
     * @return string
     * @version 0.0.3
     * @since 0.0.3
     */
    protected function getRootPath()
    {
        return vfsStream::url(self::ROOT_DIR) . '/';
    }

    /**
     * @param array $structure
     * @version 0.0.3
     * @since 0.0.3
     */
    protected static function setupVfs(array $structure)
    {
        vfsStream::setup(self::ROOT_DIR, null, $structure);
    }
}
