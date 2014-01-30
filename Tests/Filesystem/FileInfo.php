<?php

/**
 * This file is part of the ChillDev FileManager bundle.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2014 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.3
 * @since 0.1.3
 * @package ChillDev\Bundle\FileManagerBundle
 */

namespace ChillDev\Bundle\FileManagerBundle\Tests\Filesystem;

use PHPUnit_Framework_TestCase;

use ChillDev\Bundle\FileManagerBundle\Filesystem\FileInfo;
use ChillDev\Bundle\FileManagerBundle\Filesystem\Filesystem;

use org\bovigo\vfs\vfsStream;

/**
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2014 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.3
 * @since 0.1.3
 * @package ChillDev\Bundle\FileManagerBundle
 */
class FileInfoTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     * @version 0.1.3
     * @since 0.1.3
     */
    const ROOT_DIR = 'root';

    /**
     * @test
     * @version 0.1.3
     * @since 0.1.3
     */
    public function directoryMimeType()
    {
        $filename = 'foo';

        self::setupVfs([$filename => []]);

        $info = $this->getFilesystem()->getFileInfo($filename);

        $this->assertEquals(FileInfo::DIRECTORY, $info->getMimeType(), 'FileInfo::getMimeType() should return pre-defined value for directory entries.');
    }

    /**
     * @test
     * @version 0.1.3
     * @since 0.1.3
     */
    public function getMimeType()
    {
        $filename = 'foo';

        self::setupVfs([$filename => 'text']);

        $info = $this->getFilesystem()->getFileInfo($filename);

        $this->assertEquals('text/plain', $info->getMimeType(), 'FileInfo::getMimeType() should return MIME type of examined file.');
    }

    /**
     * @return Filesystem
     * @version 0.1.3
     * @since 0.1.3
     */
    protected function getFilesystem()
    {
        return new Filesystem($this->getRootPath());
    }

    /**
     * @return string
     * @version 0.1.3
     * @since 0.1.3
     */
    protected function getRootPath()
    {
        return vfsStream::url(self::ROOT_DIR) . '/';
    }

    /**
     * @param array $structure
     * @version 0.1.3
     * @since 0.1.3
     */
    protected static function setupVfs(array $structure)
    {
        vfsStream::setup(self::ROOT_DIR, null, $structure);
    }
}
