<?php

/**
 * This file is part of the ChillDev FileManager bundle.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012 - 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.2
 * @since 0.0.1
 * @package ChillDev\Bundle\FileManagerBundle
 */

namespace ChillDev\Bundle\FileManagerBundle\Tests\Controller;

use ChillDev\Bundle\FileManagerBundle\Controller\DisksController;
use ChillDev\Bundle\FileManagerBundle\Filesystem\Disk;
use ChillDev\Bundle\FileManagerBundle\Tests\BaseContainerTest;

use Symfony\Component\HttpFoundation\Request;

use org\bovigo\vfs\vfsStream;

/**
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012 - 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.2
 * @since 0.0.1
 * @package ChillDev\Bundle\FileManagerBundle
 */
class DisksControllerTest extends BaseContainerTest
{
    /**
     * @var Request
     * @version 0.0.3
     * @since 0.0.3
     */
    protected $request;

    /**
     * @version 0.0.3
     * @since 0.0.2
     */
    protected function setUpContainer()
    {
        $this->container->set('chilldev.filemanager.disks.manager', $this->manager);

        $this->request = new Request();
        $this->container->set('request', $this->request);
    }

    /**
     * Check list action.
     *
     * @test
     * @version 0.0.1
     * @since 0.0.1
     */
    public function listAction()
    {
        $controller = new DisksController();
        $controller->setContainer($this->container);

        $this->assertSame($this->manager, $controller->listAction()['disks'], 'DisksController::listAction() should return disks manager under key "disks".');
    }

    /**
     * Check directory listing.
     *
     * @test
     * @version 0.0.3
     * @since 0.0.1
     */
    public function browseAction()
    {
        $disk = $this->manager['id'];

        vfsStream::create(
            ['bar' =>
                [
                    'baz' => '0123',
                    'quux' => [],
                ],
            ]
        );

        $controller = new DisksController();
        $controller->setContainer($this->container);
        $this->request->query->replace(['by' => 'foo']);
        $return = $controller->browseAction($disk, '//./bar/../bar/.///');

        $this->assertSame($disk, $return['disk'], 'DisksController::browseAction() should return disk scope object under key "disk".');
        $this->assertEquals('bar', $return['path'], 'DisksController::browseAction() should resolve all "./" and "../" references, replace multiple "/" with single one and return computed path under key "path".');

        $this->assertCount(2, $return['list'], 'DisksController::browseAction() should return list of all files in given directory under key "list".');

        $file = $return['list']['baz'];
        $this->assertFalse($file['isDirectory'], 'DisksController::browseAction() should set "isDirectory" flag to false for file entries.');
        $this->assertEquals(4, $file['size'], 'DisksController::browseAction() should set "size" field for file entries to value of the size of given file.');
        $this->assertEquals('bar/baz', $file['path'], 'DisksController::browseAction() should set "path" field as the relative path to given file from disk.');

        $file = $return['list']['quux'];
        $this->assertTrue($file['isDirectory'], 'DisksController::browseAction() should set "isDirectory" flag to true for directory entries.');
        $this->assertArrayNotHasKey('size', $file, 'DisksController::browseAction() should not set "size" field for directory entries.');
    }

    /**
     * @test
     * @version 0.0.3
     * @since 0.0.3
     */
    public function browseActionBySizeDesc()
    {
        $disk = $this->manager['id'];

        vfsStream::create(
            ['bar' =>
                [
                    'baz' => '0123',
                    'quux' => [],
                    'corge' => '012',
                ],
            ]
        );

        $controller = new DisksController();
        $controller->setContainer($this->container);
        $this->request->query->replace(['by' => 'size', 'order' => -1]);
        $return = $controller->browseAction($disk, '//./bar/../bar/.///');

        $this->assertSame($disk, $return['disk'], 'DisksController::browseAction() should return disk scope object under key "disk".');
        $this->assertEquals('bar', $return['path'], 'DisksController::browseAction() should resolve all "./" and "../" references, replace multiple "/" with single one and return computed path under key "path".');

        $this->assertCount(3, $return['list'], 'DisksController::browseAction() should return list of all files in given directory under key "list".');

        $this->assertEquals(['baz', 'corge', 'quux'], \array_keys($return['list']), 'DisksController::browseAction() should return files references sorted by filesize in reverse order.');
    }

    /**
     * Check scope-escaping path.
     *
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\HttpException
     * @expectedExceptionMessage Directory path contains invalid reference that exceeds disk scope.
     * @version 0.0.1
     * @since 0.0.1
     */
    public function browseInvalidPath()
    {
        (new DisksController())->browseAction(new Disk('', '', ''), '/foo/../../');
    }

    /**
     * Check non-existing path.
     *
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage Directory "[Test]/test" does not exist.
     * @version 0.0.2
     * @since 0.0.1
     */
    public function browseNonexistingPath()
    {
        (new DisksController())->browseAction($this->manager['id'], 'test');
    }

    /**
     * Check non-directory path.
     *
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\HttpException
     * @expectedExceptionMessage "[Test]/foo" is not a directory.
     * @version 0.0.2
     * @since 0.0.1
     */
    public function browseNondirectoryPath()
    {
        vfsStream::create(['foo' => '']);

        (new DisksController())->browseAction($this->manager['id'], 'foo');
    }

    /**
     * Check default path parameter.
     *
     * @test
     * @version 0.0.3
     * @since 0.0.1
     */
    public function browseDefaultPath()
    {
        $controller = new DisksController();
        $controller->setContainer($this->container);
        $return = $controller->browseAction($this->manager['id']);

        $this->assertEquals('', $return['path'], 'DisksController::browseAction() should list root path of disk scope by default.');
    }
}
