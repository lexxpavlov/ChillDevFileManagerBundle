<?php

/**
 * This file is part of the ChillDev FileManager bundle.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.1
 * @since 0.0.1
 * @package ChillDev\Bundle\FileManagerBundle
 */

namespace ChillDev\Bundle\FileManagerBundle\Tests\Controller;

use DateTime;
use DateTimeZone;

use PHPUnit_Framework_TestCase;

use ChillDev\Bundle\FileManagerBundle\Controller\FilesController;
use ChillDev\Bundle\FileManagerBundle\Filesystem\Disk;
use ChillDev\Bundle\FileManagerBundle\Filesystem\Manager;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.1
 * @since 0.0.1
 * @package ChillDev\Bundle\FileManagerBundle
 */
class FilesControllerTest extends PHPUnit_Framework_TestCase
{
    /**
     * DI container.
     *
     * @var Container
     * @version 0.0.1
     * @since 0.0.1
     */
    protected $container;

    /**
     * Disks manager.
     *
     * @var Manager
     * @version 0.0.1
     * @since 0.0.1
     */
    protected $manager;

    /**
     * @version 0.0.1
     * @since 0.0.1
     */
    protected function setUp()
    {
        $this->container = new Container();
        $this->manager = new Manager();
        $this->manager->createDisk('id', 'Test', \realpath(__DIR__ . '/../fixtures/fs') . '/');

        $this->container->set('chilldev.filemanager.disks.manager', $this->manager);
    }

    /**
     * Check default behavior.
     *
     * @test
     * @version 0.0.1
     * @since 0.0.1
     */
    public function downloadAction()
    {
        // compose request
        $request = new Request();
        $this->container->set('request', $request);

        $disk = $this->manager['id'];
        $controller = new FilesController();
        $controller->setContainer($this->container);
        $response = $controller->downloadAction($disk, '//./bar/.././//foo');

        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\StreamedResponse', $response, 'FilesController::downloadAction() should return instance of type Symfony\\Component\\HttpFoundation\\StreamedResponse.');

        $time = \filemtime($disk->getSource() . 'foo');
        $date = DateTime::createFromFormat('U', $time);
        $date->setTimezone(new DateTimeZone('UTC'));

        foreach ([
            'Content-Type' => 'application/octet-stream',
            'Content-Transfer-Encoding' => 'binary',
            'Content-Length' => '4',
            'Content-Disposition' => 'attachment; filename="foo"',
            'Last-Modified' => $date->format('D, d M Y H:i:s') . ' GMT',
            'Etag' => '"' . \sha1($disk . 'foo/' . $time) . '"',
        ] as $header => $content) {
            $this->assertEquals($content, $response->headers->get($header), 'FilesController::downloadAction() should return response with ' . $header . ' header set to "' . $content . '".');
        }

        $this->expectOutputString('foo' . "\n");
        $response->sendContent();
    }

    /**
     * Check scope-escaping path.
     *
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\HttpException
     * @expectedExceptionMessage File path contains invalid reference that exceeds disk scope.
     * @version 0.0.1
     * @since 0.0.1
     */
    public function downloadInvalidPath()
    {
        (new FilesController())->downloadAction(new Disk('', '', ''), '/foo/../../');
    }

    /**
     * Check non-existing path.
     *
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage File "[Foo]/test" does not exist.
     * @version 0.0.1
     * @since 0.0.1
     */
    public function downloadNonexistingPath()
    {
        (new FilesController())->downloadAction(new Disk('', 'Foo', ''), 'test');
    }

    /**
     * Check non-file path.
     *
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\HttpException
     * @expectedExceptionMessage "[Test]/bar" is not a regular file that can be downloaded.
     * @version 0.0.1
     * @since 0.0.1
     */
    public function downloadNonfilePath()
    {
        (new FilesController())->downloadAction($this->manager['id'], 'bar');
    }

    /**
     * Check cache handling by last modification time.
     *
     * @test
     * @version 0.0.1
     * @since 0.0.1
     */
    public function downloadCachedByIfModifiedSince()
    {
        // calculate file cache info
        $disk = $this->manager['id'];
        $time = \filemtime($disk->getSource() . 'foo');
        $date = DateTime::createFromFormat('U', $time);
        $date->setTimezone(new DateTimeZone('UTC'));

        // compose request
        $request = new Request();
        $request->headers->set('If-Modified-Since', $date->format('D, d M Y H:i:s') . ' GMT');
        $this->container->set('request', $request);

        $controller = new FilesController();
        $controller->setContainer($this->container);
        $response = $controller->downloadAction($disk, 'foo');

        $this->assertEquals(304, $response->getStatusCode(), 'FilesController::downloadAction() should detect request for same file to be cached by last modification date.');
    }

    /**
     * Check cache handling by ETag.
     *
     * @test
     * @version 0.0.1
     * @since 0.0.1
     */
    public function downloadCachedByETag()
    {
        // calculate file cache info
        $disk = $this->manager['id'];
        $time = \filemtime($disk->getSource() . 'foo');

        // compose request
        $request = new Request();
        $request->headers->set('If-None-Match', '"' . \sha1($disk . 'foo/' . $time) . '"');
        $this->container->set('request', $request);

        $controller = new FilesController();
        $controller->setContainer($this->container);
        $response = $controller->downloadAction($disk, 'foo');

        $this->assertEquals(304, $response->getStatusCode(), 'FilesController::downloadAction() should detect request for same file to be cached by ETag.');
    }
}
