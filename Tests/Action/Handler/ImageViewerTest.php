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

namespace ChillDev\Bundle\FileManagerBundle\Tests\Action\Handler;

use stdClass;

use PHPUnit_Framework_TestCase;

use ChillDev\Bundle\FileManagerBundle\Action\Handler\ImageViewer;
use ChillDev\Bundle\FileManagerBundle\Filesystem\Disk;
use ChillDev\Bundle\FileManagerBundle\Tests\BaseContainerTest;

use Symfony\Component\HttpFoundation\Request;

use org\bovigo\vfs\vfsStream;

/**
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2014 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.3
 * @since 0.1.3
 * @package ChillDev\Bundle\FileManagerBundle
 */
class ImageViewerTest extends BaseContainerTest
{
    /**
     * @var Symfony\Bundle\FrameworkBundle\Templating\EngineInterface
     * @version 0.1.3
     * @since 0.1.3
     */
    protected $templating;

    /**
     * @version 0.1.3
     * @since 0.1.3
     */
    protected function setUpContainer()
    {
        parent::setUpContainer();

        $this->templating = $this->getMock('Symfony\\Bundle\\FrameworkBundle\\Templating\\EngineInterface');
    }

    /**
     * @test
     * @version 0.1.3
     * @since 0.1.3
     */
    public function getLabel()
    {
        $this->assertInternalType('string', $this->createHandler()->getLabel(), 'ImageViewer::getLabel() should return button label.');
    }

    /**
     * @test
     * @version 0.1.3
     * @since 0.1.3
     */
    public function supports()
    {
        $handler = $this->createHandler();

        $this->assertTrue($handler->supports('image/jepg'), 'ImageViewer::supports() should return TRUE for any image MIME type.');
        $this->assertTrue($handler->supports('image/gif'), 'ImageViewer::supports() should return TRUE for any image MIME type.');
        $this->assertFalse($handler->supports('text/plain'), 'ImageViewer::supports() should return FALSE for any non-image MIME type.');
    }

    /**
     * @test
     * @version 0.1.3
     * @since 0.1.3
     */
    public function handleRequest()
    {
        $path = 'foo';
        vfsStream::create([$path => 'bar']);

        // needed for closure scope
        $assert = $this;
        $toReturn = new stdClass();

        $disk = $this->manager['id'];

        $this->templating->expects($this->once())
            ->method('renderResponse')
            ->with(
                $this->equalTo('ChillDevFileManagerBundle:Action:image-viewer.html.default'),
                $this->anything(),
                $this->isNull()
            )
            ->will($this->returnCallback(function($view, $parameters) use ($assert, $toReturn, $disk, $path) {
                        $assert->assertArrayHasKey('disk', $parameters, 'ImageViewer::handle() should return disk scope object under key "disk".');
                        $assert->assertSame($disk, $parameters['disk'], 'ImageViewer::handle() should return disk scope object under key "disk".');
                        $assert->assertArrayHasKey('path', $parameters, 'ImageViewer::handle() should return path under key "path".');
                        $assert->assertSame($path, $parameters['path'], 'ImageViewer::handle() should return path under key "path".');
                        return $toReturn;
            }));

        $handler = $this->createHandler();
        $response = $handler->handle(new Request(), $disk, $path);

        $this->assertSame($toReturn, $response, 'ImageViewer::handle() should return response generated with templating service.');
    }

    /**
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\HttpException
     * @expectedExceptionMessage "[Test]/bar" is a directory.
     * @version 0.1.3
     * @since 0.1.3
     */
    public function handleNonfilePath()
    {
        vfsStream::create(['bar' => []]);

        $this->createHandler()->handle(new Request(), $this->manager['id'], 'bar');
    }

    /**
     * @return ImageViewer
     * @version 0.1.3
     * @since 0.1.3
     */
    protected function createHandler()
    {
        return new ImageViewer(
            $this->templating
        );
    }
}
