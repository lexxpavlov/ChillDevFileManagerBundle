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
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormRegistry;
use Symfony\Component\Form\ResolvedFormTypeFactory;
use Symfony\Component\Form\Extension\Core\CoreExtension;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\Validator\ConstraintValidatorFactory;
use Symfony\Component\Validator\Validator;
use Symfony\Component\Validator\Mapping\ClassMetadataFactory;

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
     * @var MockRouter
     * @version 0.0.1
     * @since 0.0.1
     */
    protected $router;

    /**
     * @var MockLogger
     * @version 0.0.1
     * @since 0.0.1
     */
    protected $logger;

    /**
     * @var MockTemplating
     * @version 0.0.1
     * @since 0.0.1
     */
    protected $templating;

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

        $this->router = new MockRouter();
        $this->container->set('router', $this->router);

        $this->logger = new MockLogger();
        $this->container->set('logger', $this->logger);

        $this->templating = new MockTemplating();
        $this->container->set('templating', $this->templating);

        $this->container->set('translator', new MockTranslator());

        $resolvedTypeFactory = new ResolvedFormTypeFactory();

        // create value validator
        $validator = new Validator(
            new ClassMetadataFactory(),
            new ConstraintValidatorFactory()
        );

        $formFactory = new FormFactory(
            new FormRegistry([
                new CoreExtension(),
                new HttpFoundationExtension(),
                new ValidatorExtension($validator),
            ], $resolvedTypeFactory), $resolvedTypeFactory
        );
        $this->container->set('form.factory', $formFactory);
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
        $this->router->toReturn = 'testroute';

        $user = 'user';
        $security = new MockSecurity(new MockToken(new MockUser($user)));
        $this->container->set('security.context', $security);

        $flashBag = new FlashBag();
        $session = new MockSession($flashBag);
        $this->container->set('session', $session);

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
        $this->assertEquals($this->router->toReturn, $response->getTargetUrl(), 'FilesController::deleteAction() should set redirect URL to result of route generator output.');
        $this->assertEquals('chilldev_filemanager_disks_browse', $this->router->route, 'FilesController::deleteAction() should set redirect URL by using "chilldev_filemanager_disks_browse" route.');
        $this->assertEquals(['disk' => $disk->getId(), 'path' => 'bar'], $this->router->arguments, 'FilesController::deleteAction() should redirect to browse action of parent directory.');

        // log properties
        $this->assertEquals('File "' . $disk . '/bar/test" deleted by user "' . $user . '".', $this->logger->message, 'FilesController::deleteAction() should log about file deletion.');
        $this->assertEquals(['realpath' => $realpath, 'scope' => $disk->getSource()], $this->logger->context, 'FilesController::deleteAction() should log file context.');

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

    /**
     * Check GET method behavior.
     *
     * @test
     * @version 0.0.1
     * @since 0.0.1
     */
    public function mkdirActionForm()
    {
        $this->templating->toReturn = new \stdClass();

        // compose request
        $request = new Request();
        $this->container->set('request', $request);

        $disk = $this->manager['id'];

        $controller = new FilesController();
        $controller->setContainer($this->container);
        $response = $controller->mkdirAction($disk, '//./bar/.././//bar');

        $this->assertEquals('ChillDevFileManagerBundle:Files:mkdir.html.config', $this->templating->view, 'FilesController::mkdirAction() should use "ChillDevFileManagerBundle:Files:mkdir.html.config" template (note the "config" proxy engine).');
        $this->assertSame($this->templating->toReturn, $response, 'FilesController::mkdirAction() should return response generated with templating service.');
        $this->assertArrayHasKey('disk', $this->templating->parameters, 'FilesController::mkdirAction() should return disk scope object under key "disk".');
        $this->assertSame($disk, $this->templating->parameters['disk'], 'FilesController::mkdirAction() should return disk scope object under key "disk".');
        $this->assertArrayHasKey('path', $this->templating->parameters, 'FilesController::mkdirAction() should return computed path under key "path".');
        $this->assertSame('bar', $this->templating->parameters['path'], 'FilesController::mkdirAction() should resolve all "./" and "../" references and replace multiple "/" with single one.');
        $this->assertArrayHasKey('form', $this->templating->parameters, 'FilesController::mkdirAction() should return form data under key "form".');
        $this->assertInstanceOf('Symfony\\Component\\Form\\FormView', $this->templating->parameters['form'], 'FilesController::mkdirAction() should return form data under key "form".');
        $this->assertEquals('mkdir', $this->templating->parameters['form']->vars['name'], 'FilesController::mkdirAction() should return form data of MkdirType form.');
        $this->assertNull($this->templating->response, 'FilesController::mkdirAction() should not pass any response and rely on generated one.');
    }

    /**
     * Check POST method behavior.
     *
     * @test
     * @version 0.0.1
     * @since 0.0.1
     */
    public function mkdirActionSubmit()
    {
        $this->router->toReturn = 'testroute2';

        $user = 'user2';
        $security = new MockSecurity(new MockToken(new MockUser($user)));
        $this->container->set('security.context', $security);

        $flashBag = new FlashBag();
        $session = new MockSession($flashBag);
        $this->container->set('session', $session);

        // compose request
        $request = new Request([], ['mkdir' => ['name' => 'mkdir']]);
        $request->setMethod('POST');
        $this->container->set('request', $request);

        $disk = $this->manager['id'];

        $realpath = $disk->getSource() . 'bar';

        if (file_exists($realpath . '/mkdir')) {
            $this->markTestSkipped('Test directory already exists.');
        }

        $controller = new FilesController();
        $controller->setContainer($this->container);
        $response = $controller->mkdirAction($disk, '//./bar/.././//bar');

        // response properties
        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\RedirectResponse', $response, 'FilesController::mkdirAction() should return instance of type Symfony\\Component\\HttpFoundation\\RedirectResponse.');
        $this->assertEquals($this->router->toReturn, $response->getTargetUrl(), 'FilesController::mkdirAction() should set redirect URL to result of route generator output.');
        $this->assertEquals('chilldev_filemanager_disks_browse', $this->router->route, 'FilesController::mkdirAction() should set redirect URL by using "chilldev_filemanager_disks_browse" route.');
        $this->assertEquals(['disk' => $disk->getId(), 'path' => 'bar'], $this->router->arguments, 'FilesController::mkdirAction() should redirect to browse action of parent directory.');

        // log properties
        $this->assertEquals('Directory "' . $disk . '/bar/mkdir" created by user "' . $user . '".', $this->logger->message, 'FilesController::mkdirAction() should log about directory creation.');
        $this->assertEquals(['realpath' => $realpath, 'scope' => $disk->getSource()], $this->logger->context, 'FilesController::mkdirAction() should log file context.');

        // flash message properties
        $flashes = $flashBag->peekAll();
        $this->assertArrayHasKey('done', $flashes, 'FilesController::mkdirAction() should set flash message of type "done".');
        $this->assertCount(1, $flashes['done'], 'FilesController::mkdirAction() should set flash message of type "done".');
        $this->assertEquals('"' . $disk . '/bar/mkdir" has been created.', $flashes['done'][0], 'FilesController::mkdirAction() should set flash message that notifies about directory creation.');

        // result assertions
        $realpath .= '/mkdir';
        $this->assertFileExists($realpath, 'FilesController::mkdirAction() should create new directory.');

        $realpath = \realpath($realpath);
        $this->assertTrue(\is_dir($realpath), 'FilesController::mkdirAction() should create new directory.');

        \rmdir($realpath);
    }

    /**
     * Check POST method behavior on invalid data.
     *
     * @test
     * @version 0.0.1
     * @since 0.0.1
     */
    public function mkdirActionInvalidSubmit()
    {
        $this->templating->toReturn = new \stdClass();

        // compose request
        $request = new Request([], ['mkdir' => ['name' => '']]);
        $request->setMethod('POST');
        $this->container->set('request', $request);

        $disk = $this->manager['id'];

        $controller = new FilesController();
        $controller->setContainer($this->container);
        $response = $controller->mkdirAction($disk, '//./bar/.././//bar');

        $this->assertSame($this->templating->toReturn, $response, 'FilesController::mkdirAction() should render form view when invalid data is submitted.');
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
     * @expectedExceptionMessage Directory "[Foo]/test" does not exist.
     * @version 0.0.1
     * @since 0.0.1
     */
    public function mkdirNonexistingPath()
    {
        (new FilesController())->mkdirAction(new Disk('', 'Foo', ''), 'test');
    }

    /**
     * Check non-directory path.
     *
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\HttpException
     * @expectedExceptionMessage "[Test]/foo" is not a directory, so a sub-directory can't be created within it.
     * @version 0.0.1
     * @since 0.0.1
     */
    public function mkdirNondirectoryPath()
    {
        (new FilesController())->mkdirAction($this->manager['id'], 'foo');
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

class MockTemplating
{
    public $view;
    public $parameters;
    public $response;
    public $toReturn;

    public function renderResponse($view, $parameters, $response)
    {
        $this->view = $view;
        $this->parameters = $parameters;
        $this->response = $response;

        return $this->toReturn;
    }
}
