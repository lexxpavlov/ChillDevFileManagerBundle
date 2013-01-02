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

namespace ChillDev\Bundle\FileManagerBundle\Tests\Templating;

use PHPUnit_Framework_TestCase;

use ChillDev\Bundle\FileManagerBundle\Templating\ConfigEngine;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\TemplateNameParser;

/**
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012 - 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.2
 * @since 0.0.1
 * @package ChillDev\Bundle\FileManagerBundle
 */
class ConfigEngineTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Container
     * @version 0.0.2
     * @since 0.0.1
     */
    protected $container;

    /**
     * @var ConfigEngine
     * @version 0.0.1
     * @since 0.0.1
     */
    protected $templating;

    /**
     * @var string
     * @version 0.0.1
     * @since 0.0.1
     */
    protected $engine = 'foo';

    /**
     * @version 0.0.1
     * @since 0.0.1
     */
    protected function setUp()
    {
        $this->container = new Container();
        $this->templating = new ConfigEngine($this->container, new TemplateNameParser(), $this->engine);
    }

    /**
     * @test
     * @version 0.0.2
     * @since 0.0.1
     */
    public function renderSubsequentEngine()
    {
        // needed for closure scope
        $assert = $this;
        $engine = $this->engine;
        $toReturn = new \stdClass();

        $parameters = ['bar' => 'baz'];

        $templating = $this->createTemplatingMock();
        $templating->expects($this->once())
            ->method('render')
            ->with($this->anything(), $this->equalTo($parameters))
            ->will($this->returnCallback(function($view) use ($assert, $engine, $toReturn) {
                    $assert->assertEquals($engine, $view->get('engine'), 'ConfigEngine::render() should call sub-sequent engine render() method on template with same name but engine replaced to configured one.');
                    return $toReturn;
            }));
        $return = $this->templating->render('qux.config', $parameters);

        $this->assertSame($toReturn, $return, 'ConfigEngine::render() should return result of sub-sequent engine render() method.');
    }

    /**
     * @test
     * @version 0.0.2
     * @since 0.0.1
     */
    public function renderDefaultParameters()
    {
        // needed for closure scope
        $assert = $this;
        $engine = $this->engine;

        $templating = $this->createTemplatingMock();
        $templating->expects($this->once())
            ->method('render')
            ->with($this->anything(), $this->isEmpty())
            ->will($this->returnCallback(function($view) use ($assert, $engine) {
                    $assert->assertEquals($engine, $view->get('engine'), 'ConfigEngine::render() should call sub-sequent engine render() method on template with same name but engine replaced to configured one.');
            }));
        $this->templating->render('qux.config');
    }

    /**
     * @test
     * @version 0.0.2
     * @since 0.0.1
     */
    public function existsSubsequentEngine()
    {
        // needed for closure scope
        $assert = $this;
        $engine = $this->engine;
        $toReturn = new \stdClass();

        $templating = $this->createTemplatingMock();
        $templating->expects($this->once())
            ->method('exists')
            ->with($this->anything())
            ->will($this->returnCallback(function($view) use ($assert, $engine, $toReturn) {
                    $assert->assertEquals($engine, $view->get('engine'), 'ConfigEngine::exists() should call sub-sequent engine exists() method on template with same name but engine replaced to configured one.');
                    return $toReturn;
            }));
        $return = $this->templating->exists('qux.config');

        $this->assertSame($toReturn, $return, 'ConfigEngine::exists() should return result of sub-sequent engine exists() method.');
    }

    /**
     * @test
     * @version 0.0.1
     * @since 0.0.1
     */
    public function supportsConfigEngine()
    {
        $this->assertTrue($this->templating->supports('qux.config'), 'ConfigEngine::supports() should handle "*.config" templates.');
        $this->assertFalse($this->templating->supports('qux.twig'), 'ConfigEngine::supports() should not handle templates other then "*.config".');
    }

    /**
     * @test
     * @version 0.0.2
     * @since 0.0.1
     */
    public function renderResponseSubsequentEngine()
    {
        // needed for closure scope
        $assert = $this;
        $engine = $this->engine;

        $parameters = ['bar' => 'baz'];
        $response = new Response();
        $templating = $this->createTemplatingMock();

        $templating->expects($this->once())
            ->method('renderResponse')
            ->with($this->anything(), $this->equalTo($parameters), $this->identicalTo($response))
            ->will($this->returnCallback(function($view, $parameters, $response) use ($assert, $engine) {
                    $assert->assertEquals($engine, $view->get('engine'), 'ConfigEngine::renderResponse() should call sub-sequent engine renderResponse() method on template with same name but engine replaced to configured one.');
                    return $response;
            }));
        $return = $this->templating->renderResponse('qux.config', $parameters, $response);

        $this->assertSame($response, $return, 'ConfigEngine::renderResponse() should return response object of sub-sequent engine renderResponse() method.');
    }

    /**
     * @test
     * @version 0.0.2
     * @since 0.0.1
     */
    public function renderResponseDefaultParameters()
    {
        // needed for closure scope
        $assert = $this;
        $engine = $this->engine;

        $templating = $this->createTemplatingMock();

        $templating->expects($this->once())
            ->method('renderResponse')
            ->with($this->anything(), $this->isEmpty(), $this->isNull())
            ->will($this->returnCallback(function($view) use ($assert, $engine) {
                    $assert->assertEquals($engine, $view->get('engine'), 'ConfigEngine::renderResponse() should call sub-sequent engine renderResponse() method on template with same name but engine replaced to configured one.');
                    return new Response();
            }));
        $return = $this->templating->renderResponse('qux.config');

        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\Response', $return, 'ConfigEngine::renderResponse() should always return instance of type Symfony\\Component\\HttpFoundation\\Response.');
    }

    /**
     * @return Symfony\Bundle\FrameworkBundle\Templating\EngineInterface
     * @version 0.0.2
     * @since 0.0.2
     */
    protected function createTemplatingMock()
    {
        $templating = $this->getMock('Symfony\\Bundle\\FrameworkBundle\\Templating\\EngineInterface');
        $this->container->set('templating', $templating);
        return $templating;
    }

    /**
     * @test
     * @expectedException LogicException
     * @expectedExceptionMessage Template "qux.config" cannot be streamed as the sub-sequent target engine "MockTemplating_StreamWithoutSupport" configured to handle it does not implement StreamingEngineInterface.
     * @version 0.0.2
     * @since 0.0.1
     */
    public function streamWithoutSupport()
    {
        $templating = $this->getMock('Symfony\\Bundle\\FrameworkBundle\\Templating\\EngineInterface', [], [], 'MockTemplating_StreamWithoutSupport');
        $this->container->set('templating', $templating);

        $this->templating->stream('qux.config');
    }

    /**
     * @test
     * @version 0.0.2
     * @since 0.0.1
     */
    public function streamSubsequentEngine()
    {
        // needed for closure scope
        $assert = $this;
        $engine = $this->engine;

        $parameters = ['bar' => 'baz'];

        $templating = $this->getMock('Symfony\\Component\\Templating\\StreamingEngineInterface');
        $this->container->set('templating', $templating);

        $templating->expects($this->once())
            ->method('stream')
            ->with($this->anything(), $this->equalTo($parameters))
            ->will($this->returnCallback(function($view) use ($assert, $engine) {
                    $assert->assertEquals($engine, $view->get('engine'), 'ConfigEngine::stream() should call sub-sequent engine stream() method on template with same name but engine replaced to configured one.');
            }));
        $this->templating->stream('qux.config', $parameters);
    }

    /**
     * @test
     * @version 0.0.2
     * @since 0.0.1
     */
    public function streamDefaultParameters()
    {
        // needed for closure scope
        $assert = $this;
        $engine = $this->engine;

        $templating = $this->getMock('Symfony\\Component\\Templating\\StreamingEngineInterface');
        $this->container->set('templating', $templating);

        $templating->expects($this->once())
            ->method('stream')
            ->with($this->anything(), $this->isEmpty())
            ->will($this->returnCallback(function($view) use ($assert, $engine) {
                    $assert->assertEquals($engine, $view->get('engine'), 'ConfigEngine::stream() should call sub-sequent engine stream() method on template with same name but engine replaced to configured one.');
            }));
        $this->templating->stream('qux.config');
    }
}
