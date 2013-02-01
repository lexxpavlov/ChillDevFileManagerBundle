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

use DateTime;
use DateTimeZone;

use ChillDev\Bundle\FileManagerBundle\Controller\FilesController;
use ChillDev\Bundle\FileManagerBundle\Filesystem\Disk;
use ChillDev\Bundle\FileManagerBundle\Tests\BaseContainerTest;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;

use org\bovigo\vfs\vfsStream;

/**
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012 - 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.2
 * @since 0.0.1
 * @package ChillDev\Bundle\FileManagerBundle
 */
class FilesControllerTest extends BaseContainerTest
{
    /**
     * @var Symfony\Component\Routing\RouterInterface
     * @version 0.0.2
     * @since 0.0.1
     */
    protected $router;

    /**
     * @var Symfony\Bridge\Monolog\Logger
     * @version 0.0.2
     * @since 0.0.1
     */
    protected $logger;

    /**
     * @var Symfony\Bundle\FrameworkBundle\Templating\EngineInterface
     * @version 0.0.2
     * @since 0.0.1
     */
    protected $templating;

    /**
     * @var Request
     * @version 0.0.2
     * @since 0.0.2
     */
    protected $request;

    /**
     * @var Disk
     * @version 0.0.2
     * @since 0.0.2
     */
    protected $user;

    /**
     * @var Symfony\Component\HttpFoundation\Session\Session
     * @version 0.0.2
     * @since 0.0.2
     */
    protected $session;

    /**
     * @version 0.0.2
     * @since 0.0.2
     */
    protected function setUpContainer()
    {
        parent::setUpContainer();

        $this->router = $this->getMock('Symfony\\Component\\Routing\\RouterInterface');
        $this->container->set('router', $this->router);

        $this->logger = $this->getMock('Symfony\\Bridge\\Monolog\\Logger', [], [], '', false);
        $this->container->set('logger', $this->logger);

        $this->templating = $this->getMock('Symfony\\Bundle\\FrameworkBundle\\Templating\\EngineInterface');
        $this->container->set('templating', $this->templating);

        $this->user = $user = new Disk('user', '', '');
        $token = $this->getMock('Symfony\\Component\\Security\\Core\\Authentication\\Token\\TokenInterface');
        $token->expects($this->any())
            ->method('getUser')
            ->will($this->returnCallback(function() use ($user) {
                        return $user;
            }));

        $security = $this->getMock('Symfony\\Component\\Security\\Core\\SecurityContext', null, [], '', false);
        $security->setToken($token);
        $this->container->set('security.context', $security);

        $this->session = $this->getMock('Symfony\\Component\\HttpFoundation\\Session\\Session');
        $this->container->set('session', $this->session);

        $this->request = new Request();
        $this->container->set('request', $this->request);
    }

    /**
     * @param array $headers
     * @param string $method
     * @param array $request
     * @version 0.0.2
     * @since 0.0.2
     */
    protected function setRequest(array $headers = [], $method = 'GET', array $request = [])
    {
        $this->request->headers->replace($headers);
        $this->request->setMethod($method);
        $this->request->request->replace($request);
    }

    /**
     * Check default behavior.
     *
     * @test
     * @version 0.0.2
     * @since 0.0.1
     */
    public function downloadAction()
    {
        $content = 'bar';

        vfsStream::create(['foo' => $content]);

        // compose request
        $this->setRequest();

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
            'Content-Length' => \strlen($content),
            'Content-Disposition' => 'attachment; filename="foo"',
            'Last-Modified' => $date->format('D, d M Y H:i:s') . ' GMT',
            'Etag' => '"' . \sha1($disk . 'foo/' . $time) . '"',
        ] as $header => $value) {
            $this->assertEquals($value, $response->headers->get($header), 'FilesController::downloadAction() should return response with ' . $header . ' header set to "' . $value . '".');
        }

        $this->expectOutputString($content);
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
     * @expectedExceptionMessage File "[Test]/test" does not exist.
     * @version 0.0.2
     * @since 0.0.1
     */
    public function downloadNonexistingPath()
    {
        (new FilesController())->downloadAction($this->manager['id'], 'test');
    }

    /**
     * Check non-file path.
     *
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\HttpException
     * @expectedExceptionMessage "[Test]/bar" is not a regular file that can be downloaded.
     * @version 0.0.2
     * @since 0.0.1
     */
    public function downloadNonfilePath()
    {
        vfsStream::create(['bar' => []]);

        (new FilesController())->downloadAction($this->manager['id'], 'bar');
    }

    /**
     * Check cache handling by last modification time.
     *
     * @test
     * @version 0.0.2
     * @since 0.0.1
     */
    public function downloadCachedByIfModifiedSince()
    {
        vfsStream::create(['foo' => '']);

        // calculate file cache info
        $disk = $this->manager['id'];
        $time = \filemtime($disk->getSource() . 'foo');
        $date = DateTime::createFromFormat('U', $time);
        $date->setTimezone(new DateTimeZone('UTC'));

        // compose request
        $this->setRequest(['If-Modified-Since' => $date->format('D, d M Y H:i:s') . ' GMT']);

        $controller = new FilesController();
        $controller->setContainer($this->container);
        $response = $controller->downloadAction($disk, 'foo');

        $this->assertEquals(304, $response->getStatusCode(), 'FilesController::downloadAction() should detect request for same file to be cached by last modification date.');
    }

    /**
     * Check cache handling by ETag.
     *
     * @test
     * @version 0.0.2
     * @since 0.0.1
     */
    public function downloadCachedByETag()
    {
        vfsStream::create(['foo' => '']);

        // calculate file cache info
        $disk = $this->manager['id'];
        $time = \filemtime($disk->getSource() . 'foo');

        // compose request
        $this->setRequest(['If-None-Match' => '"' . \sha1($disk . 'foo/' . $time) . '"']);

        $controller = new FilesController();
        $controller->setContainer($this->container);
        $response = $controller->downloadAction($disk, 'foo');

        $this->assertEquals(304, $response->getStatusCode(), 'FilesController::downloadAction() should detect request for same file to be cached by ETag.');
    }

    /**
     * Check default behavior.
     *
     * @test
     * @version 0.0.2
     * @since 0.0.1
     */
    public function deleteAction()
    {
        vfsStream::create(['bar' => ['test' => '']]);

        $toReturn = 'testroute';
        $flashBag = new FlashBag();

        // compose request
        $this->setRequest();

        $disk = $this->manager['id'];
        $realpath = $disk->getSource() . 'bar/test';

        // mocks set-up
        $this->router->expects($this->once())
            ->method('generate')
            ->with(
                $this->equalTo('chilldev_filemanager_disks_browse'),
                $this->equalTo(['disk' => $disk->getId(), 'path' => 'bar'])
            )
            ->will($this->returnValue($toReturn));
        $this->logger->expects($this->once())
            ->method('info')
            ->with(
                $this->equalTo('File "bar/test" deleted by user "' . $this->user->__toString() . '".'),
                $this->equalTo(['scope' => $disk->getSource()])
            );
        $this->session->expects($this->once())
            ->method('getFlashBag')
            ->will($this->returnValue($flashBag));

        $controller = new FilesController();
        $controller->setContainer($this->container);
        $response = $controller->deleteAction($disk, '//./bar/.././//bar/test');

        // response properties
        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\RedirectResponse', $response, 'FilesController::deleteAction() should return instance of type Symfony\\Component\\HttpFoundation\\RedirectResponse.');
        $this->assertEquals($toReturn, $response->getTargetUrl(), 'FilesController::deleteAction() should set redirect URL to result of route generator output.');

        // flash message properties
        $flashes = $flashBag->peekAll();
        $this->assertArrayHasKey('done', $flashes, 'FilesController::deleteAction() should set flash message of type "done".');
        $this->assertCount(1, $flashes['done'], 'FilesController::deleteAction() should set flash message of type "done".');
        $this->assertEquals('"' . $disk . '/bar/test" has been deleted.', $flashes['done'][0], 'FilesController::deleteAction() should set flash message that notifies about file deletion.');

        // result assertions
        $this->assertFileNotExists($realpath, 'FilesController::deleteAction() should delete the file.');
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
     * @expectedExceptionMessage File "[Test]/test" does not exist.
     * @version 0.0.2
     * @since 0.0.1
     */
    public function deleteNonexistingPath()
    {
        (new FilesController())->deleteAction($this->manager['id'], 'test');
    }

    /**
     * Check non-file path.
     *
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\HttpException
     * @expectedExceptionMessage "[Test]/bar" is not a regular file that can be deleted.
     * @version 0.0.2
     * @since 0.0.1
     */
    public function deleteNonfilePath()
    {
        vfsStream::create(['bar' => []]);

        (new FilesController())->deleteAction($this->manager['id'], 'bar');
    }

    /**
     * Check GET method behavior.
     *
     * @test
     * @version 0.0.2
     * @since 0.0.1
     */
    public function mkdirActionForm()
    {
        vfsStream::create(['bar' => []]);

        // needed for closure scope
        $assert = $this;
        $toReturn = new \stdClass();

        // compose request
        $this->setRequest();

        $disk = $this->manager['id'];

        $this->templating->expects($this->once())
            ->method('renderResponse')
            ->with(
                $this->equalTo('ChillDevFileManagerBundle:Files:mkdir.html.config'),
                $this->anything(),
                $this->isNull()
            )
            ->will($this->returnCallback(function($view, $parameters) use ($assert, $toReturn, $disk) {
                        $assert->assertArrayHasKey('disk', $parameters, 'FilesController::mkdirAction() should return disk scope object under key "disk".');
                        $assert->assertSame($disk, $parameters['disk'], 'FilesController::mkdirAction() should return disk scope object under key "disk".');
                        $assert->assertArrayHasKey('path', $parameters, 'FilesController::mkdirAction() should return computed path under key "path".');
                        $assert->assertSame('bar', $parameters['path'], 'FilesController::mkdirAction() should resolve all "./" and "../" references and replace multiple "/" with single one.');
                        $assert->assertArrayHasKey('form', $parameters, 'FilesController::mkdirAction() should return form data under key "form".');
                        $assert->assertInstanceOf('Symfony\\Component\\Form\\FormView', $parameters['form'], 'FilesController::mkdirAction() should return form data under key "form".');
                        $assert->assertEquals('mkdir', $parameters['form']->vars['name'], 'FilesController::mkdirAction() should return form data of MkdirType form.');
                        return $toReturn;
            }));

        $controller = new FilesController();
        $controller->setContainer($this->container);
        $response = $controller->mkdirAction($disk, '//./bar/.././//bar');

        $this->assertSame($toReturn, $response, 'FilesController::mkdirAction() should return response generated with templating service.');
    }

    /**
     * Check POST method behavior.
     *
     * @test
     * @version 0.0.2
     * @since 0.0.1
     */
    public function mkdirActionSubmit()
    {
        vfsStream::create(['bar' => []]);

        $toReturn = 'testroute2';
        $flashBag = new FlashBag();

        // compose request
        $this->setRequest([], 'POST', ['mkdir' => ['name' => 'mkdir']]);

        $disk = $this->manager['id'];

        $realpath = $disk->getSource() . 'bar';

        // mocks set-up
        $this->router->expects($this->once())
            ->method('generate')
            ->with(
                $this->equalTo('chilldev_filemanager_disks_browse'),
                $this->equalTo(['disk' => $disk->getId(), 'path' => 'bar'])
            )
            ->will($this->returnValue($toReturn));
        $this->logger->expects($this->once())
            ->method('info')
            ->with(
                $this->equalTo('Directory "bar/mkdir" created by user "' . $this->user->__toString() . '".'),
                $this->equalTo(['scope' => $disk->getSource()])
            );
        $this->session->expects($this->once())
            ->method('getFlashBag')
            ->will($this->returnValue($flashBag));

        $controller = new FilesController();
        $controller->setContainer($this->container);
        $response = $controller->mkdirAction($disk, '//./bar/.././//bar');

        // response properties
        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\RedirectResponse', $response, 'FilesController::mkdirAction() should return instance of type Symfony\\Component\\HttpFoundation\\RedirectResponse.');
        $this->assertEquals($toReturn, $response->getTargetUrl(), 'FilesController::mkdirAction() should set redirect URL to result of route generator output.');

        // flash message properties
        $flashes = $flashBag->peekAll();
        $this->assertArrayHasKey('done', $flashes, 'FilesController::mkdirAction() should set flash message of type "done".');
        $this->assertCount(1, $flashes['done'], 'FilesController::mkdirAction() should set flash message of type "done".');
        $this->assertEquals('"' . $disk . '/bar/mkdir" has been created.', $flashes['done'][0], 'FilesController::mkdirAction() should set flash message that notifies about directory creation.');

        // result assertions
        $realpath .= '/mkdir';
        $this->assertFileExists($realpath, 'FilesController::mkdirAction() should create new directory.');
        $this->assertTrue(\is_dir($realpath), 'FilesController::mkdirAction() should create new directory.');
    }

    /**
     * Check POST method behavior on invalid data.
     *
     * @test
     * @version 0.0.2
     * @since 0.0.1
     */
    public function mkdirActionInvalidSubmit()
    {
        vfsStream::create(['bar' => []]);

        $toReturn = new \stdClass();

        // compose request
        $this->setRequest([], 'POST', ['mkdir' => ['name' => '']]);

        $disk = $this->manager['id'];

        $this->templating->expects($this->once())
            ->method('renderResponse')
            ->will($this->returnValue($toReturn));

        $controller = new FilesController();
        $controller->setContainer($this->container);
        $response = $controller->mkdirAction($disk, '//./bar/.././//bar');

        $this->assertSame($toReturn, $response, 'FilesController::mkdirAction() should render form view when invalid data is submitted.');
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
    public function mkdirInvalidPath()
    {
        (new FilesController())->mkdirAction(new Disk('', '', ''), '/foo/../../');
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
    public function mkdirNonexistingPath()
    {
        (new FilesController())->mkdirAction($this->manager['id'], 'test');
    }

    /**
     * Check non-directory path.
     *
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\HttpException
     * @expectedExceptionMessage "[Test]/foo" is not a directory, so a sub-directory can't be created within it.
     * @version 0.0.2
     * @since 0.0.1
     */
    public function mkdirNondirectoryPath()
    {
        vfsStream::create(['foo' => '']);

        (new FilesController())->mkdirAction($this->manager['id'], 'foo');
    }
}
