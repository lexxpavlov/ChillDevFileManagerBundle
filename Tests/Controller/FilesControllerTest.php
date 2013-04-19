<?php

/**
 * This file is part of the ChillDev FileManager bundle.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012 - 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.1
 * @since 0.0.1
 * @package ChillDev\Bundle\FileManagerBundle
 */

namespace ChillDev\Bundle\FileManagerBundle\Tests\Controller;

use DateTime;
use DateTimeZone;
use ReflectionClass;

use ChillDev\Bundle\FileManagerBundle\Controller\FilesController;
use ChillDev\Bundle\FileManagerBundle\Filesystem\Disk;
use ChillDev\Bundle\FileManagerBundle\Tests\BaseContainerTest;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;

use org\bovigo\vfs\vfsStream;

/**
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012 - 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.1
 * @since 0.0.1
 * @package ChillDev\Bundle\FileManagerBundle
 */
class FilesControllerTest extends BaseContainerTest
{
    /**
     * @var string
     * @version 0.1.1
     * @since 0.1.1
     */
    protected static $className = 'ChillDev\\Bundle\\FileManagerBundle\\Controller\\FilesController';

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
     * @version 0.0.3
     * @since 0.0.2
     */
    protected function setRequest(array $headers = [], $method = 'GET', array $request = [])
    {
        $this->request->headers->replace($headers);
        $this->request->setMethod($method);
        $this->request->request->replace($request);
        $this->request->files->replace();
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
     * @version 0.1.1
     * @since 0.0.1
     */
    public function deleteAction()
    {
        vfsStream::create(['bar' => ['test' => '']]);

        $toReturn = 'testroute';

        // compose request
        $this->setRequest();

        $disk = $this->manager['id'];
        $realpath = $disk->getSource() . 'bar/test';

        // mocks set-up
        $this->logger->expects($this->once())
            ->method('info')
            ->with(
                $this->equalTo('File "bar/test" deleted by user "' . $this->user->__toString() . '".'),
                $this->equalTo(['scope' => $disk->getSource()])
            );

        $controller = $this->getMockController(['addFlashMessage', 'redirectToDirectory']);
        $controller->expects($this->once())
            ->method('addFlashMessage')
            ->with(
                $this->equalTo('done'),
                $this->isType('string'),
                $this->arrayHasKey('%file%')
            );
        $controller->expects($this->once())
            ->method('redirectToDirectory')
            ->with(
                $this->identicalTo($disk),
                $this->equalTo('bar')
            )
            ->will($this->returnValue(new RedirectResponse($toReturn)));

        $controller->setContainer($this->container);
        $response = $controller->deleteAction($disk, '//./bar/.././//bar/test');

        // response properties
        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\RedirectResponse', $response, 'FilesController::deleteAction() should return instance of type Symfony\\Component\\HttpFoundation\\RedirectResponse.');
        $this->assertEquals($toReturn, $response->getTargetUrl(), 'FilesController::deleteAction() should set redirect URL to result of route generator output.');

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
     * Check GET method behavior.
     *
     * @test
     * @version 0.1.1
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
                $this->equalTo('ChillDevFileManagerBundle:Files:mkdir.html.default'),
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
     * @version 0.1.1
     * @since 0.0.1
     */
    public function mkdirActionSubmit()
    {
        vfsStream::create(['bar' => []]);

        $toReturn = 'testroute2';

        // compose request
        $this->setRequest([], 'POST', ['mkdir' => ['name' => 'mkdir']]);

        $disk = $this->manager['id'];

        $realpath = $disk->getSource() . 'bar';

        // mocks set-up
        $this->logger->expects($this->once())
            ->method('info')
            ->with(
                $this->equalTo('Directory "bar/mkdir" created by user "' . $this->user->__toString() . '".'),
                $this->equalTo(['scope' => $disk->getSource()])
            );

        $controller = $this->getMockController(['addFlashMessage', 'redirectToDirectory']);
        $controller->expects($this->once())
            ->method('addFlashMessage')
            ->with(
                $this->equalTo('done'),
                $this->isType('string'),
                $this->arrayHasKey('%directory%')
            );
        $controller->expects($this->once())
            ->method('redirectToDirectory')
            ->with(
                $this->identicalTo($disk),
                $this->equalTo('bar')
            )
            ->will($this->returnValue(new RedirectResponse($toReturn)));

        $controller->setContainer($this->container);
        $response = $controller->mkdirAction($disk, '//./bar/.././//bar');

        // response properties
        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\RedirectResponse', $response, 'FilesController::mkdirAction() should return instance of type Symfony\\Component\\HttpFoundation\\RedirectResponse.');
        $this->assertEquals($toReturn, $response->getTargetUrl(), 'FilesController::mkdirAction() should set redirect URL to result of route generator output.');

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
     * @expectedExceptionMessage File path contains invalid reference that exceeds disk scope.
     * @version 0.0.3
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
     * @expectedExceptionMessage File "[Test]/test" does not exist.
     * @version 0.0.3
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

    /**
     * Check GET method behavior.
     *
     * @test
     * @version 0.1.1
     * @since 0.0.3
     */
    public function uploadActionForm()
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
                $this->equalTo('ChillDevFileManagerBundle:Files:upload.html.default'),
                $this->anything(),
                $this->isNull()
            )
            ->will($this->returnCallback(function($view, $parameters) use ($assert, $toReturn, $disk) {
                        $assert->assertArrayHasKey('disk', $parameters, 'FilesController::uploadAction() should return disk scope object under key "disk".');
                        $assert->assertSame($disk, $parameters['disk'], 'FilesController::uploadAction() should return disk scope object under key "disk".');
                        $assert->assertArrayHasKey('path', $parameters, 'FilesController::uploadAction() should return computed path under key "path".');
                        $assert->assertSame('bar', $parameters['path'], 'FilesController::uploadAction() should resolve all "./" and "../" references and replace multiple "/" with single one.');
                        $assert->assertArrayHasKey('form', $parameters, 'FilesController::uploadAction() should return form data under key "form".');
                        $assert->assertInstanceOf('Symfony\\Component\\Form\\FormView', $parameters['form'], 'FilesController::uploadAction() should return form data under key "form".');
                        $assert->assertEquals('upload', $parameters['form']->vars['name'], 'FilesController::uploadAction() should return form data of UploadType form.');
                        return $toReturn;
            }));

        $controller = new FilesController();
        $controller->setContainer($this->container);
        $response = $controller->uploadAction($disk, '//./bar/.././//bar');

        $this->assertSame($toReturn, $response, 'FilesController::uploadAction() should return response generated with templating service.');
    }

    /**
     * Check POST method behavior.
     *
     * @test
     * @version 0.1.1
     * @since 0.0.3
     */
    public function uploadActionSubmit()
    {
        vfsStream::create(['bar' => []]);

        $toReturn = 'testroute3';

        $file = $this->getMock('Symfony\\Component\\HttpFoundation\\File\\UploadedFile', [], [], '', false);
        $file->expects($this->once())
            ->method('move');

        // compose request
        $this->setRequest([], 'POST', ['upload' => ['name' => 'upload']]);
        $this->request->files->replace([
            'upload' => [
                'file' => $file,
            ],
        ]);

        $disk = $this->manager['id'];

        $realpath = $disk->getSource() . 'bar';

        // mocks set-up
        $this->logger->expects($this->once())
            ->method('info')
            ->with(
                $this->equalTo('File "bar/upload" uploaded by user "' . $this->user->__toString() . '".'),
                $this->equalTo(['scope' => $disk->getSource()])
            );

        $controller = $this->getMockController(['addFlashMessage', 'redirectToDirectory']);
        $controller->expects($this->once())
            ->method('addFlashMessage')
            ->with(
                $this->equalTo('done'),
                $this->isType('string'),
                $this->arrayHasKey('%file%')
            );
        $controller->expects($this->once())
            ->method('redirectToDirectory')
            ->with(
                $this->identicalTo($disk),
                $this->equalTo('bar')
            )
            ->will($this->returnValue(new RedirectResponse($toReturn)));

        $controller->setContainer($this->container);
        $response = $controller->uploadAction($disk, '//./bar/.././//bar');

        // response properties
        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\RedirectResponse', $response, 'FilesController::uploadAction() should return instance of type Symfony\\Component\\HttpFoundation\\RedirectResponse.');
        $this->assertEquals($toReturn, $response->getTargetUrl(), 'FilesController::uploadAction() should set redirect URL to result of route generator output.');
    }

    /**
     * Check POST method behavior on invalid data.
     *
     * @test
     * @version 0.0.3
     * @since 0.0.3
     */
    public function uploadActionInvalidSubmit()
    {
        vfsStream::create(['bar' => []]);

        $toReturn = new \stdClass();

        // compose request
        $this->setRequest([], 'POST', ['upload' => ['name' => '']]);

        $disk = $this->manager['id'];

        $this->templating->expects($this->once())
            ->method('renderResponse')
            ->will($this->returnValue($toReturn));

        $controller = new FilesController();
        $controller->setContainer($this->container);
        $response = $controller->uploadAction($disk, '//./bar/.././//bar');

        $this->assertSame($toReturn, $response, 'FilesController::uploadAction() should render form view when invalid data is submitted.');
    }

    /**
     * Check scope-escaping path.
     *
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\HttpException
     * @expectedExceptionMessage File path contains invalid reference that exceeds disk scope.
     * @version 0.0.3
     * @since 0.0.3
     */
    public function uploadInvalidPath()
    {
        (new FilesController())->uploadAction(new Disk('', '', ''), '/foo/../../');
    }

    /**
     * Check non-existing path.
     *
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage File "[Test]/test" does not exist.
     * @version 0.0.3
     * @since 0.0.3
     */
    public function uploadNonexistingPath()
    {
        (new FilesController())->uploadAction($this->manager['id'], 'test');
    }

    /**
     * Check non-directory path.
     *
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\HttpException
     * @expectedExceptionMessage "[Test]/foo" is not a directory, so a file can't be uploaded into it.
     * @version 0.0.3
     * @since 0.0.3
     */
    public function uploadNondirectoryPath()
    {
        vfsStream::create(['foo' => '']);

        (new FilesController())->uploadAction($this->manager['id'], 'foo');
    }

    /**
     * Check GET method behavior.
     *
     * @test
     * @version 0.1.1
     * @since 0.0.3
     */
    public function renameActionForm()
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
                $this->equalTo('ChillDevFileManagerBundle:Files:rename.html.default'),
                $this->anything(),
                $this->isNull()
            )
            ->will($this->returnCallback(function($view, $parameters) use ($assert, $toReturn, $disk) {
                        $assert->assertArrayHasKey('disk', $parameters, 'FilesController::renameAction() should return disk scope object under key "disk".');
                        $assert->assertSame($disk, $parameters['disk'], 'FilesController::renameAction() should return disk scope object under key "disk".');
                        $assert->assertArrayHasKey('path', $parameters, 'FilesController::renameAction() should return computed path under key "path".');
                        $assert->assertSame('bar', $parameters['path'], 'FilesController::renameAction() should resolve all "./" and "../" references and replace multiple "/" with single one.');
                        $assert->assertArrayHasKey('form', $parameters, 'FilesController::renameAction() should return form data under key "form".');
                        $assert->assertInstanceOf('Symfony\\Component\\Form\\FormView', $parameters['form'], 'FilesController::renameAction() should return form data under key "form".');
                        $assert->assertEquals('rename', $parameters['form']->vars['name'], 'FilesController::renameAction() should return form data of RenameType form.');
                        return $toReturn;
            }));

        $controller = new FilesController();
        $controller->setContainer($this->container);
        $response = $controller->renameAction($disk, '//./bar/.././//bar');

        $this->assertSame($toReturn, $response, 'FilesController::renameAction() should return response generated with templating service.');
    }

    /**
     * Check POST method behavior.
     *
     * @test
     * @version 0.1.1
     * @since 0.0.3
     */
    public function renameActionSubmit()
    {
        vfsStream::create(['bar' => []]);

        $toReturn = 'testroute4';

        // compose request
        $this->setRequest([], 'POST', ['rename' => ['name' => 'foo']]);

        $disk = $this->manager['id'];

        $realpath1 = $disk->getSource() . 'bar';
        $realpath2 = $disk->getSource() . 'foo';

        // mocks set-up
        $this->logger->expects($this->once())
            ->method('info')
            ->with(
                $this->equalTo('File "bar" renamed to "foo" by user "' . $this->user->__toString() . '".'),
                $this->equalTo(['scope' => $disk->getSource()])
            );

        $controller = $this->getMockController(['addFlashMessage', 'redirectToDirectory']);
        $controller->expects($this->once())
            ->method('addFlashMessage')
            ->with(
                $this->equalTo('done'),
                $this->isType('string'),
                $this->logicalAnd($this->arrayHasKey('%file%'), $this->arrayHasKey('%name%'))
            );
        $controller->expects($this->once())
            ->method('redirectToDirectory')
            ->with(
                $this->identicalTo($disk),
                $this->equalTo('.')
            )
            ->will($this->returnValue(new RedirectResponse($toReturn)));

        $controller->setContainer($this->container);
        $response = $controller->renameAction($disk, '//./bar/.././//bar');

        // response properties
        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\RedirectResponse', $response, 'FilesController::renameAction() should return instance of type Symfony\\Component\\HttpFoundation\\RedirectResponse.');
        $this->assertEquals($toReturn, $response->getTargetUrl(), 'FilesController::renameAction() should set redirect URL to result of route generator output.');

        // result assertions
        $this->assertFileNotExists($realpath1, 'FilesController::renameAction() should rename file from old name to new one.');
        $this->assertFileExists($realpath2, 'FilesController::renameAction() should rename file from old name to new one.');
    }

    /**
     * Check POST method behavior on invalid data.
     *
     * @test
     * @version 0.0.3
     * @since 0.0.3
     */
    public function renameActionInvalidSubmit()
    {
        vfsStream::create(['bar' => []]);

        $toReturn = new \stdClass();

        // compose request
        $this->setRequest([], 'POST', ['rename' => ['name' => '']]);

        $disk = $this->manager['id'];

        $this->templating->expects($this->once())
            ->method('renderResponse')
            ->will($this->returnValue($toReturn));

        $controller = new FilesController();
        $controller->setContainer($this->container);
        $response = $controller->renameAction($disk, '//./bar/.././//bar');

        $this->assertSame($toReturn, $response, 'FilesController::renameAction() should render form view when invalid data is submitted.');
    }

    /**
     * Check scope-escaping path.
     *
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\HttpException
     * @expectedExceptionMessage File path contains invalid reference that exceeds disk scope.
     * @version 0.0.3
     * @since 0.0.3
     */
    public function renameInvalidPath()
    {
        (new FilesController())->renameAction(new Disk('', '', ''), '/foo/../../');
    }

    /**
     * Check non-existing path.
     *
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage File "[Test]/test" does not exist.
     * @version 0.0.3
     * @since 0.0.3
     */
    public function renameNonexistingPath()
    {
        (new FilesController())->renameAction($this->manager['id'], 'test');
    }

    /**
     * Check GET method behavior.
     *
     * @test
     * @version 0.1.1
     * @since 0.0.3
     */
    public function moveAction()
    {
        vfsStream::create(['foo' => '', 'bar' => [], 'baz' => []]);

        // needed for closure scope
        $assert = $this;
        $toReturn = new \stdClass();

        // compose request
        $this->setRequest();
        $this->request->query->replace(['order' => -1]);

        $disk = $this->manager['id'];

        $this->templating->expects($this->once())
            ->method('renderResponse')
            ->with(
                $this->equalTo('ChillDevFileManagerBundle:Files:destination.html.default'),
                $this->anything(),
                $this->isNull()
            )
            ->will($this->returnCallback(function($view, $parameters) use ($assert, $toReturn, $disk) {
                        $assert->assertArrayHasKey('disk', $parameters, 'FilesController::moveAction() should return disk scope object under key "disk".');
                        $assert->assertSame($disk, $parameters['disk'], 'FilesController::moveAction() should return disk scope object under key "disk".');
                        $assert->assertArrayHasKey('path', $parameters, 'FilesController::moveAction() should return computed path under key "path".');
                        $assert->assertSame('bar', $parameters['path'], 'FilesController::moveAction() should resolve all "./" and "../" references and replace multiple "/" with single one.');
                        $assert->assertArrayHasKey('destination', $parameters, 'FilesController::moveAction() should return computed destination under key "path".');
                        $assert->assertEquals('', $parameters['destination'], 'FilesController::moveAction() should resolve all "./" and "../" references and replace multiple "/" with single one.');
                        $assert->assertArrayHasKey('route', $parameters, 'FilesController::moveAction() should return target route under key "route".');
                        $assert->assertSame('chilldev_filemanager_files_move', $parameters['route'], 'FilesController::moveAction() should return target route under key "route".');
                        $assert->assertArrayHasKey('title', $parameters, 'FilesController::moveAction() should return page title under key "title".');
                        $assert->assertSame('Moving file %disk%/%path%', $parameters['title'], 'FilesController::moveAction() should return page title under key "title".');

                        // directories list assertions
                        $assert->assertArrayHasKey('list', $parameters, 'FilesController::moveAction() should return list of directories under key "list".');
                        $assert->assertCount(2, $parameters['list'], 'FilesController::moveAction() should return list of all directories in given destination under key "list".');
                        $assert->assertEquals(['baz', 'bar'], \array_keys($parameters['list']), 'FilesController::moveAction() should return directories references sorted by name in reverse order.');

                        return $toReturn;
            }));

        $controller = new FilesController();
        $controller->setContainer($this->container);
        $response = $controller->moveAction($disk, '//./bar/.././//bar', '');

        $this->assertSame($toReturn, $response, 'FilesController::moveAction() should return response generated with templating service.');
    }

    /**
     * Check POST method behavior.
     *
     * @test
     * @version 0.1.1
     * @since 0.0.3
     */
    public function moveActionSubmit()
    {
        vfsStream::create(['foo' => '', 'bar' => []]);

        $toReturn = 'testroute5';

        // compose request
        $this->setRequest([], 'POST');

        $disk = $this->manager['id'];

        $realpath1 = $disk->getSource() . 'foo';
        $realpath2 = $disk->getSource() . 'bar/foo';

        // mocks set-up
        $this->logger->expects($this->once())
            ->method('info')
            ->with(
                $this->equalTo('File "foo" moved to "bar" by user "' . $this->user->__toString() . '".'),
                $this->equalTo(['scope' => $disk->getSource()])
            );

        $controller = $this->getMockController(['addFlashMessage', 'redirectToDirectory']);
        $controller->expects($this->once())
            ->method('addFlashMessage')
            ->with(
                $this->equalTo('done'),
                $this->isType('string'),
                $this->logicalAnd($this->arrayHasKey('%file%'), $this->arrayHasKey('%destination%'))
            );
        $controller->expects($this->once())
            ->method('redirectToDirectory')
            ->with(
                $this->identicalTo($disk),
                $this->equalTo('.')
            )
            ->will($this->returnValue(new RedirectResponse($toReturn)));

        $controller->setContainer($this->container);
        $response = $controller->moveAction($disk, '//./foo/.././//foo', 'bar');

        // response properties
        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\RedirectResponse', $response, 'FilesController::moveAction() should return instance of type Symfony\\Component\\HttpFoundation\\RedirectResponse.');
        $this->assertEquals($toReturn, $response->getTargetUrl(), 'FilesController::moveAction() should set redirect URL to result of route generator output.');

        // result assertions
        $this->assertFileNotExists($realpath1, 'FilesController::moveAction() should move file from old location to new one.');
        $this->assertFileExists($realpath2, 'FilesController::moveAction() should move file from old location to new one.');
    }

    /**
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\HttpException
     * @expectedExceptionMessage "[Test]/foo" is not a directory.
     * @version 0.0.3
     * @since 0.0.3
     */
    public function moveActionToNonDirectoryDestination()
    {
        vfsStream::create(['foo' => '', 'bar' => []]);

        (new FilesController())->moveAction($this->manager['id'], '//./bar/.././//bar', 'foo');
    }

    /**
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\HttpException
     * @expectedExceptionMessage File path contains invalid reference that exceeds disk scope.
     * @version 0.0.3
     * @since 0.0.3
     */
    public function moveInvalidPath()
    {
        (new FilesController())->moveAction(new Disk('', '', ''), '/foo/../../', '');
    }

    /**
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\HttpException
     * @expectedExceptionMessage File path contains invalid reference that exceeds disk scope.
     * @version 0.0.3
     * @since 0.0.3
     */
    public function moveInvalidDestination()
    {
        (new FilesController())->moveAction(new Disk('', '', ''), '', '/foo/../../');
    }

    /**
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage File "[Test]/test" does not exist.
     * @version 0.0.3
     * @since 0.0.3
     */
    public function moveNonexistingPath()
    {
        (new FilesController())->moveAction($this->manager['id'], 'test', '');
    }

    /**
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage File "[Test]/test" does not exist.
     * @version 0.0.3
     * @since 0.0.3
     */
    public function moveNonexistingDestination()
    {
        vfsStream::create(['bar' => []]);

        (new FilesController())->moveAction($this->manager['id'], 'bar', 'test');
    }

    /**
     * Check GET method behavior.
     *
     * @test
     * @version 0.1.1
     * @since 0.0.3
     */
    public function copyAction()
    {
        vfsStream::create(['foo' => '', 'bar' => [], 'baz' => []]);

        // needed for closure scope
        $assert = $this;
        $toReturn = new \stdClass();

        // compose request
        $this->setRequest();
        $this->request->query->replace(['order' => -1]);

        $disk = $this->manager['id'];

        $this->templating->expects($this->once())
            ->method('renderResponse')
            ->with(
                $this->equalTo('ChillDevFileManagerBundle:Files:destination.html.default'),
                $this->anything(),
                $this->isNull()
            )
            ->will($this->returnCallback(function($view, $parameters) use ($assert, $toReturn, $disk) {
                        $assert->assertArrayHasKey('disk', $parameters, 'FilesController::copyAction() should return disk scope object under key "disk".');
                        $assert->assertSame($disk, $parameters['disk'], 'FilesController::copyAction() should return disk scope object under key "disk".');
                        $assert->assertArrayHasKey('path', $parameters, 'FilesController::copyAction() should return computed path under key "path".');
                        $assert->assertSame('bar', $parameters['path'], 'FilesController::copyAction() should resolve all "./" and "../" references and replace multiple "/" with single one.');
                        $assert->assertArrayHasKey('destination', $parameters, 'FilesController::copyAction() should return computed destination under key "path".');
                        $assert->assertEquals('', $parameters['destination'], 'FilesController::copyAction() should resolve all "./" and "../" references and replace multiple "/" with single one.');
                        $assert->assertArrayHasKey('route', $parameters, 'FilesController::copyActioncopyAction() should return target route under key "route".');
                        $assert->assertSame('chilldev_filemanager_files_copy', $parameters['route'], 'FilesController::copyAction() should return target route under key "route".');
                        $assert->assertArrayHasKey('title', $parameters, 'FilesController::copyAction() should return page title under key "title".');
                        $assert->assertSame('Copying file %disk%/%path%', $parameters['title'], 'FilesController::copyAction() should return page title under key "title".');

                        // directories list assertions
                        $assert->assertArrayHasKey('list', $parameters, 'FilesController::copyAction() should return list of directories under key "list".');
                        $assert->assertCount(2, $parameters['list'], 'FilesController::copyAction() should return list of all directories in given destination under key "list".');
                        $assert->assertEquals(['baz', 'bar'], \array_keys($parameters['list']), 'FilesController::copyAction() should return directories references sorted by name in reverse order.');

                        return $toReturn;
            }));

        $controller = new FilesController();
        $controller->setContainer($this->container);
        $response = $controller->copyAction($disk, '//./bar/.././//bar', '');

        $this->assertSame($toReturn, $response, 'FilesController::copyAction() should return response generated with templating service.');
    }

    /**
     * Check POST method behavior.
     *
     * @test
     * @version 0.1.1
     * @since 0.0.3
     */
    public function copyActionSubmit()
    {
        vfsStream::create(['foo' => ['baz' => ''], 'bar' => []]);

        $toReturn = 'testroute6';

        // compose request
        $this->setRequest([], 'POST');

        $disk = $this->manager['id'];

        $realpath1 = $disk->getSource() . 'foo';
        $realpath2 = $disk->getSource() . 'bar/foo';
        $realpath3 = $disk->getSource() . 'foo/baz';
        $realpath4 = $disk->getSource() . 'bar/foo/baz';

        // mocks set-up
        $this->logger->expects($this->once())
            ->method('info')
            ->with(
                $this->equalTo('File "foo" copied to "bar" by user "' . $this->user->__toString() . '".'),
                $this->equalTo(['scope' => $disk->getSource()])
            );

        $controller = $this->getMockController(['addFlashMessage', 'redirectToDirectory']);
        $controller->expects($this->once())
            ->method('addFlashMessage')
            ->with(
                $this->equalTo('done'),
                $this->isType('string'),
                $this->logicalAnd($this->arrayHasKey('%file%'), $this->arrayHasKey('%destination%'))
            );
        $controller->expects($this->once())
            ->method('redirectToDirectory')
            ->with(
                $this->identicalTo($disk),
                $this->equalTo('.')
            )
            ->will($this->returnValue(new RedirectResponse($toReturn)));

        $controller->setContainer($this->container);
        $response = $controller->copyAction($disk, '//./foo/.././//foo', 'bar');

        // response properties
        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\RedirectResponse', $response, 'FilesController::copyAction() should return instance of type Symfony\\Component\\HttpFoundation\\RedirectResponse.');
        $this->assertEquals($toReturn, $response->getTargetUrl(), 'FilesController::copyAction() should set redirect URL to result of route generator output.');

        // result assertions
        $this->assertFileExists($realpath1, 'FilesController::copyAction() should create a copy of a file from old location in new one.');
        $this->assertFileExists($realpath2, 'FilesController::copyAction() should create a copy of a file from old location in new one.');
        $this->assertFileExists($realpath3, 'FilesController::copyAction() should create a copy of a file from old location in new one.');
        $this->assertFileExists($realpath4, 'FilesController::copyAction() should create a copy of a file from old location in new one.');
    }

    /**
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\HttpException
     * @expectedExceptionMessage "[Test]/foo" is not a directory.
     * @version 0.0.3
     * @since 0.0.3
     */
    public function copyActionToNonDirectoryDestination()
    {
        vfsStream::create(['foo' => '', 'bar' => []]);

        (new FilesController())->copyAction($this->manager['id'], '//./bar/.././//bar', 'foo');
    }

    /**
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\HttpException
     * @expectedExceptionMessage File path contains invalid reference that exceeds disk scope.
     * @version 0.0.3
     * @since 0.0.3
     */
    public function copyInvalidPath()
    {
        (new FilesController())->copyAction(new Disk('', '', ''), '/foo/../../', '');
    }

    /**
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\HttpException
     * @expectedExceptionMessage File path contains invalid reference that exceeds disk scope.
     * @version 0.0.3
     * @since 0.0.3
     */
    public function copyInvalidDestination()
    {
        (new FilesController())->copyAction(new Disk('', '', ''), '', '/foo/../../');
    }

    /**
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage File "[Test]/test" does not exist.
     * @version 0.0.3
     * @since 0.0.3
     */
    public function copyNonexistingPath()
    {
        (new FilesController())->copyAction($this->manager['id'], 'test', '');
    }

    /**
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage File "[Test]/test" does not exist.
     * @version 0.0.3
     * @since 0.0.3
     */
    public function copyNonexistingDestination()
    {
        vfsStream::create(['bar' => []]);

        (new FilesController())->copyAction($this->manager['id'], 'bar', 'test');
    }

    /**
     * @test
     * @version 0.1.1
     * @since 0.1.1
     */
    public function addFlashMessage()
    {
        $type = 'foo';
        $value = 'baz';
        $flashBag = new FlashBag();

        // mocks set-up
        $this->session->expects($this->once())
            ->method('getFlashBag')
            ->will($this->returnValue($flashBag));

        $controller = new FilesController();
        $controller->setContainer($this->container);

        // get protected method
        $method = self::getMethod('addFlashMessage');
        $method->invoke(
            $controller,
            $type,
            '%foo% bar',
            ['%foo%' => $value]
        );

        // flash message properties
        $flashes = $flashBag->peekAll();
        $this->assertArrayHasKey($type, $flashes, 'FilesController::addFlashMessage() should set flash message of given type.');
        $this->assertCount(1, $flashes[$type], 'FilesController::addFlashMessage() should set flash message of given type.');
        $this->assertEquals($value . ' bar', $flashes[$type][0], 'FilesController::addFlashMessage() should set flash message.');        
    }

    /**
     * @test
     * @version 0.1.1
     * @since 0.1.1
     */
    public function redirectToDirectory()
    {
        // pre-defined values
        $disk = $this->manager['id'];
        $toReturn = 'value';
        $path = 'bar';

        $controller = $this->getMockController(['generateUrl']);
        $controller->expects($this->once())
            ->method('generateUrl')
            ->with(
                $this->equalTo('chilldev_filemanager_disks_browse'),
                $this->equalTo(['disk' => $disk->getId(), 'path' => $path])
            )
            ->will($this->returnValue($toReturn));

        $controller->setContainer($this->container);

        // get protected method
        $method = self::getMethod('redirectToDirectory');
        $response = $method->invoke(
            $controller,
            $disk,
            $path
        );

        // response properties
        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\RedirectResponse', $response, 'FilesController::redirectToDirectory() should return instance of type Symfony\\Component\\HttpFoundation\\RedirectResponse.');
        $this->assertEquals($toReturn, $response->getTargetUrl(), 'FilesController::redirectToDirectory() should set redirect URL to result of route generator output.');
    }

    /**
     * @param string $method
     * @return \ReflectionMethod
     * @version 0.1.1
     * @since 0.1.1
     */
    protected static function getMethod($method)
    {
        $class = new ReflectionClass(self::$className);
        $method = $class->getMethod($method);
        $method->setAccessible(true);
        return $method;
    }

    /**
     * @param string[] $methods
     * @return FilesController
     * @version 0.1.1
     * @since 0.1.1
     */
    protected function getMockController(array $methods = [])
    {
        return $this->getMock(self::$className, $methods);
    }
}
