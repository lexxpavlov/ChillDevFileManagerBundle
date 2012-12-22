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
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;

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

    /**
     * Check default behavior.
     *
     * @test
     * @version 0.0.1
     * @since 0.0.1
     */
    public function deleteAction()
    {
        $router = new MockRouter();
        $router->toReturn = 'testroute';
        $this->container->set('router', $router);

        $logger = new MockLogger();
        $this->container->set('logger', $logger);

        $user = 'user';
        $security = new MockSecurity(new MockToken(new MockUser($user)));
        $this->container->set('security.context', $security);

        $flashBag = new FlashBag();
        $session = new MockSession($flashBag);
        $this->container->set('session', $session);

        $this->container->set('translator', new MockTranslator());

        // compose request
        $request = new Request();
        $this->container->set('request', $request);

        $disk = $this->manager['id'];
        $realpath = $disk->getSource() . 'bar/test';
        \touch($realpath);
        $realpath = \realpath($realpath);

        if (!\file_exists($realpath)) {
            $this->markTestSkipped('Failed to create test file.');
        }

        $controller = new FilesController();
        $controller->setContainer($this->container);
        $response = $controller->deleteAction($disk, '//./bar/.././//bar/test');

        // response properties
        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\RedirectResponse', $response, 'FilesController::deleteAction() should return instance of type Symfony\\Component\\HttpFoundation\\RedirectResponse.');
        $this->assertEquals($router->toReturn, $response->getTargetUrl(), 'FilesController::deleteAction() should set redirect URL to result of route generator output.');
        $this->assertEquals('chilldev_filemanager_disks_browse', $router->route, 'FilesController::deleteAction() should set redirect URL by using "chilldev_filemanager_disks_browse" route.');
        $this->assertEquals(['disk' => $disk->getId(), 'path' => 'bar'], $router->arguments, 'FilesController::deleteAction() should redirect to browse action of parent directory.');

        // log properties
        $this->assertEquals('File "' . $disk . '/bar/test" deleted by user "' . $user . '".', $logger->message, 'FilesController::deleteAction() should log about file deletion.');
        $this->assertEquals(['realpath' => $realpath, 'scope' => $disk->getSource()], $logger->context, 'FilesController::deleteAction() should log file context.');

        // flash message properties
        $flashes = $flashBag->peekAll();
        $this->assertArrayHasKey('done', $flashes, 'FilesController::deleteAction() should set flash message of type "done".');
        $this->assertCount(1, $flashes['done'], 'FilesController::deleteAction() should set flash message of type "done".');
        $this->assertEquals('"bar/test" has been deleted.', $flashes['done'][0], 'FilesController::deleteAction() should set flash message that notifies about file deletion.');

        // result assertions
        $this->assertFalse(\file_exists($realpath), 'FilesController::deleteAction() should delete the file.');
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
    public function deleteInvalidPath()
    {
        (new FilesController())->deleteAction(new Disk('', '', ''), '/foo/../../');
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
    public function deleteNonexistingPath()
    {
        (new FilesController())->deleteAction(new Disk('', 'Foo', ''), 'test');
    }

    /**
     * Check non-file path.
     *
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\HttpException
     * @expectedExceptionMessage "[Test]/bar" is not a regular file that can be deleted.
     * @version 0.0.1
     * @since 0.0.1
     */
    public function deleteNonfilePath()
    {
        (new FilesController())->deleteAction($this->manager['id'], 'bar');
    }
}

class MockRouter
{
    public $route;
    public $arguments;
    public $toReturn;

    public function generate($route, $arguments)
    {
        $this->route = $route;
        $this->arguments = $arguments;
        return $this->toReturn;
    }
}

class MockLogger
{
    public $message;
    public $context;

    public function info($message, $context)
    {
        $this->message = $message;
        $this->context = $context;
    }
}

class MockUser
{
    public $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function __toString()
    {
        return $this->name;
    }
}

class MockToken
{
    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }
}

class MockSecurity
{
    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function getToken()
    {
        return $this->token;
    }
}

class MockSession
{
    public $flashBag;

    public function __construct($flashBag)
    {
        $this->flashBag = $flashBag;
    }

    public function getFlashBag()
    {
        return $this->flashBag;
    }
}

class MockTranslator
{
    public function trans($message, $params = [])
    {
        foreach ($params as $key => $value) {
            $message = \str_replace($key, $value, $message);
        }

        return $message;
    }
}
