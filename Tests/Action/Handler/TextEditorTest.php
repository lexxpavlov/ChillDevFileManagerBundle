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

use ChillDev\Bundle\FileManagerBundle\Action\Handler\TextEditor;
use ChillDev\Bundle\FileManagerBundle\Filesystem\Disk;
use ChillDev\Bundle\FileManagerBundle\Tests\BaseContainerTest;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;

use org\bovigo\vfs\vfsStream;

/**
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2014 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.3
 * @since 0.1.3
 * @package ChillDev\Bundle\FileManagerBundle
 */
class TextEditorTest extends BaseContainerTest
{
    /**
     * @var Symfony\Component\Routing\RouterInterface
     * @version 0.1.3
     * @since 0.1.3
     */
    protected $router;

    /**
     * @var Symfony\Bundle\FrameworkBundle\Templating\EngineInterface
     * @version 0.1.3
     * @since 0.1.3
     */
    protected $templating;

    /**
     * @var Symfony\Component\HttpFoundation\Session\Session
     * @version 0.1.3
     * @since 0.1.3
     */
    protected $session;

    /**
     * @var Symfony\Component\Translation\TranslatorInterface
     * @version 0.1.3
     * @since 0.1.3
     */
    protected $translator;

    /**
     * @version 0.1.3
     * @since 0.1.3
     */
    protected function setUpContainer()
    {
        parent::setUpContainer();

        $this->router = $this->getMock('Symfony\\Component\\Routing\\RouterInterface');
        $this->templating = $this->getMock('Symfony\\Bundle\\FrameworkBundle\\Templating\\EngineInterface');
        $this->session = $this->getMock('Symfony\\Component\\HttpFoundation\\Session\\Session');
        $this->translator = $this->getMock('Symfony\\Component\\Translation\\TranslatorInterface');
    }

    /**
     * @test
     * @version 0.1.3
     * @since 0.1.3
     */
    public function getLabel()
    {
        $this->assertInternalType('string', $this->createHandler()->getLabel(), 'TextEditor::getLabel() should return button label.');
    }

    /**
     * @test
     * @version 0.1.3
     * @since 0.1.3
     */
    public function supports()
    {
        $handler = $this->createHandler();

        $this->assertTrue($handler->supports('text/plain'), 'TextEditor::supports() should return TRUE for any text MIME type.');
        $this->assertTrue($handler->supports('text/html'), 'TextEditor::supports() should return TRUE for any text MIME type.');
        $this->assertFalse($handler->supports('image/jpeg'), 'TextEditor::supports() should return FALSE for any non-text MIME type.');
    }

    /**
     * @test
     * @version 0.1.3
     * @since 0.1.3
     */
    public function handleGetRequest()
    {
        $path = 'foo';
        $content = 'bar';
        vfsStream::create([$path => $content]);

        // needed for closure scope
        $assert = $this;
        $toReturn = new stdClass();

        $disk = $this->manager['id'];

        $this->templating->expects($this->once())
            ->method('renderResponse')
            ->with(
                $this->equalTo('ChillDevFileManagerBundle:Action:text-editor.html.default'),
                $this->anything(),
                $this->isNull()
            )
            ->will($this->returnCallback(function($view, $parameters) use ($assert, $toReturn, $disk, $path) {
                        $assert->assertArrayHasKey('disk', $parameters, 'TextEditor::handle() should return disk scope object under key "disk".');
                        $assert->assertSame($disk, $parameters['disk'], 'TextEditor::handle() should return disk scope object under key "disk".');
                        $assert->assertArrayHasKey('path', $parameters, 'TextEditor::handle() should return path under key "path".');
                        $assert->assertSame($path, $parameters['path'], 'TextEditor::handle() should return path under key "path".');
                        $assert->assertArrayHasKey('form', $parameters, 'TextEditor::handle() should return form data under key "form".');
                        $assert->assertInstanceOf('Symfony\\Component\\Form\\FormView', $parameters['form'], 'TextEditor::handle() should return form data under key "form".');
                        $assert->assertEquals('action_edit', $parameters['form']->vars['name'], 'TextEditor::handle() should return form data of EditorType form.');
                        return $toReturn;
            }));

        //TODO: assert for form factory call with initial file content

        $handler = $this->createHandler();
        $response = $handler->handle(new Request(), $disk, $path);

        $this->assertSame($toReturn, $response, 'TextEditor::handle() should return response generated with templating service.');
    }

    /**
     * @test
     * @version 0.1.3
     * @since 0.1.3
     */
    public function handlePostRequest()
    {
        $path = 'foo';
        $content = 'bar';
        vfsStream::create([$path => $path . $content . $content]);

        $toReturn = 'testeditor1';

        // compose request
        $request = new Request([], ['action_edit' => ['content' => $content]]);
        $request->setMethod('POST');

        $disk = $this->manager['id'];

        $realpath = $disk->getSource() . $path;

        $this->router->expects($this->once())
            ->method('generate')
            ->with(
                $this->equalTo('chilldev_filemanager_disks_browse'),
                $this->logicalAnd($this->arrayHasKey('disk'), $this->arrayHasKey('path'))
            )
            ->will($this->returnValue($toReturn));

        // mocks set-up
        $flashBag = new FlashBag();
        $this->session->expects($this->once())
            ->method('getFlashBag')
            ->will($this->returnValue($flashBag));

        $response = $this->createHandler()->handle($request, $disk, $path);

        // response properties
        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\RedirectResponse', $response, 'TextEditor::handle() should return instance of type Symfony\\Component\\HttpFoundation\\RedirectResponse.');
        $this->assertEquals($toReturn, $response->getTargetUrl(), 'TextEditor::handle() should set redirect URL to result of route generator output.');

        // result assertions
        $this->assertStringEqualsFile($realpath, $content, 'TextEditor::handle() should update file contents.');
    }

    /**
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\HttpException
     * @expectedExceptionMessage "[Test]/bar" is not a regular file that can be edited.
     * @version 0.1.3
     * @since 0.1.3
     */
    public function handleNonfilePath()
    {
        vfsStream::create(['bar' => []]);

        $this->createHandler()->handle(new Request(), $this->manager['id'], 'bar');
    }

    /**
     * @return TextEditor
     * @version 0.1.3
     * @since 0.1.3
     */
    protected function createHandler()
    {
        return new TextEditor(
            $this->templating,
            $this->router,
            $this->session,
            $this->translator,
            $this->container->get('form.factory')
        );
    }
}
