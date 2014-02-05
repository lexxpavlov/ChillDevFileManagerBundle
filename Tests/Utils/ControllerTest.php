<?php

/**
 * This file is part of the ChillDev FileManager bundle.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2013 - 2014 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.3
 * @since 0.0.3
 * @package ChillDev\Bundle\FileManagerBundle
 */

namespace ChillDev\Bundle\FileManagerBundle\Tests\Utils;

use Exception;

use ChillDev\Bundle\FileManagerBundle\Filesystem\Disk;
use ChillDev\Bundle\FileManagerBundle\Tests\BaseManagerTest;
use ChillDev\Bundle\FileManagerBundle\Utils\Controller;

use org\bovigo\vfs\vfsStream;

/**
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2013 - 2014 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.3
 * @since 0.0.3
 * @package ChillDev\Bundle\FileManagerBundle
 */
class ControllerTest extends BaseManagerTest
{
    /**
     * @test
     * @version 0.0.3
     * @since 0.0.3
     */
    public function validPathResolving()
    {
        $this->assertEquals('foo/bar', Controller::resolvePath('foo/./bar'), 'Controller::resolvePath() should resolve symbolic elements of path.');
    }

    /**
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\HttpException
     * @expectedExceptionMessage File path contains invalid reference that exceeds disk scope.
     * @version 0.0.3
     * @since 0.0.3
     */
    public function invalidPathResolving()
    {
        Controller::resolvePath('..');
    }

    /**
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage File "[Test]/foo" does not exist.
     * @version 0.0.3
     * @since 0.0.3
     */
    public function existChecking()
    {
        Controller::ensureExist($this->manager['id'], $this->manager['id']->getFilesystem(), 'foo');
    }

    /**
     * @test
     * @version 0.1.3
     * @since 0.1.3
     */
    public function ensureDirectoryFlag()
    {
        vfsStream::create(['foo' => '', 'bar' => []]);

        $disk = $this->manager['id'];
        $filesystem = $disk->getFilesystem();

        Controller::ensureDirectoryFlag($disk, $filesystem, 'bar');
        Controller::ensureDirectoryFlag($disk, $filesystem, 'foo', false);

        $this->assertTrue(true, 'Controller::ensureDirectoryFlag() should succeed for all correct states.');
    }

    /**
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\HttpException
     * @expectedExceptionMessage "[Test]/bar" is a directory.
     * @version 0.1.3
     * @since 0.1.3
     */
    public function ensureDirectoryFlagFalseForDirectory()
    {
        vfsStream::create(['bar' => []]);

        $disk = $this->manager['id'];
        $filesystem = $disk->getFilesystem();

        Controller::ensureDirectoryFlag($disk, $filesystem, 'bar', false);
    }

    /**
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\HttpException
     * @expectedExceptionMessage "[Test]/foo" is not a directory.
     * @version 0.1.3
     * @since 0.1.3
     */
    public function ensureDirectoryFlagTrueForNonDirectory()
    {
        vfsStream::create(['foo' => '']);

        $disk = $this->manager['id'];
        $filesystem = $disk->getFilesystem();

        Controller::ensureDirectoryFlag($disk, $filesystem, 'foo');
    }

    /**
     * @test
     * @version 0.0.3
     * @since 0.0.3
     */
    public function sortingReverse()
    {
        $sorter = Controller::getSorter('field', -1);

        $data = [
            'foo' => ['field' => 'a'],
            'bar' => ['field' => 'b'],
            'baz' => [],
            'quux' => ['field' => 'd'],
            'qux' => ['field' => 'c'],
            'corge' => [],
        ];
        \uasort($data, $sorter);

        $this->assertEquals(['quux', 'qux', 'bar', 'foo', 'baz', 'corge'], \array_keys($data), 'Controller::getSorter() should return sorting callback that sort associative arrays.');
    }
}
