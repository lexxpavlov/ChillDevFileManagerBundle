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

namespace ChillDev\Bundle\FileManagerBundle\Tests\Templating;

use PHPUnit_Framework_TestCase;

use ChillDev\Bundle\FileManagerBundle\Templating\ConfigEngine;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\StreamingEngineInterface;
use Symfony\Component\Templating\TemplateNameParser;

/**
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.1
 * @since 0.0.1
 * @package ChillDev\Bundle\FileManagerBundle
 */
class ConfigEngineTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerBuilder
     * @version 0.0.1
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
        $this->container = new ContainerBuilder();
        $this->templating = new ConfigEngine($this->container, new TemplateNameParser(), $this->engine);
    }

    /**
     * @test
     * @version 0.0.1
     * @since 0.0.1
     */
    public function renderSubsequentEngine()
    {
        $parameters = ['bar' => 'baz'];
        $toReturn = new \stdClass();

        $templating = new MockTemplatingEngine();
        $templating->toReturn = $toReturn;
        $this->container->set('templating', $templating);

        $return = $this->templating->render('qux.config', $parameters);
        $this->assertEquals('render', $templating->lastCall, 'ConfigEngine::render() should call sub-sequent engine render() method.');
        $this->assertEquals($this->engine, $templating->lastName->get('engine'), 'ConfigEngine::render() should call sub-sequent engine render() method on template with same name but engine replaced to configured one.');
        $this->assertEquals($parameters, $templating->lastParameters, 'ConfigEngine::render() should pass all parameters to sub-sequent engine render() method.');
        $this->assertSame($toReturn, $return, 'ConfigEngine::render() should return result of sub-sequent engine render() method.');
    }

    /**
     * @test
     * @version 0.0.1
     * @since 0.0.1
     */
    public function renderDefaultParameters()
    {
        $templating = new MockTemplatingEngine();
        $this->container->set('templating', $templating);

        $this->templating->render('qux.config');
        $this->assertEquals('render', $templating->lastCall, 'ConfigEngine::render() should call sub-sequent engine render() method.');
        $this->assertEquals($this->engine, $templating->lastName->get('engine'), 'ConfigEngine::render() should call sub-sequent engine render() method on template with same name but engine replaced to configured one.');
        $this->assertEquals([], $templating->lastParameters, 'ConfigEngine::render() should pass empty array as parameters by default to sub-sequent engine render() method.');
    }

    /**
     * @test
     * @version 0.0.1
     * @since 0.0.1
     */
    public function existsSubsequentEngine()
    {
        $toReturn = new \stdClass();

        $templating = new MockTemplatingEngine();
        $templating->toReturn = $toReturn;
        $this->container->set('templating', $templating);

        $return = $this->templating->exists('qux.config');
        $this->assertEquals('exists', $templating->lastCall, 'ConfigEngine::exists() should call sub-sequent engine exists() method.');
        $this->assertEquals($this->engine, $templating->lastName->get('engine'), 'ConfigEngine::exists() should call sub-sequent engine exists() method on template with same name but engine replaced to configured one.');
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
     * @version 0.0.1
     * @since 0.0.1
     */
    public function renderResponseSubsequentEngine()
    {
        $parameters = ['bar' => 'baz'];
        $response = new Response();

        $templating = new MockTemplatingEngine();
        $this->container->set('templating', $templating);

        $return = $this->templating->renderResponse('qux.config', $parameters, $response);
        $this->assertEquals('renderResponse', $templating->lastCall, 'ConfigEngine::renderResponse() should call sub-sequent engine renderResponse() method.');
        $this->assertEquals($this->engine, $templating->lastName->get('engine'), 'ConfigEngine::renderResponse() should call sub-sequent engine renderResponse() method on template with same name but engine replaced to configured one.');
        $this->assertEquals($parameters, $templating->lastParameters, 'ConfigEngine::renderResponse() should pass all parameters to sub-sequent engine renderResponse() method.');
        $this->assertSame($response, $templating->lastResponse, 'ConfigEngine::renderResponse() should pass response object to sub-sequent engine renderResponse() method.');
        $this->assertSame($response, $return, 'ConfigEngine::renderResponse() should return response object of sub-sequent engine renderResponse() method.');
    }

    /**
     * @test
     * @version 0.0.1
     * @since 0.0.1
     */
    public function renderResponseDefaultParameters()
    {
        $templating = new MockTemplatingEngine();
        $this->container->set('templating', $templating);

        $return = $this->templating->renderResponse('qux.config');
        $this->assertEquals('renderResponse', $templating->lastCall, 'ConfigEngine::renderResponse() should call sub-sequent engine renderResponse() method.');
        $this->assertEquals($this->engine, $templating->lastName->get('engine'), 'ConfigEngine::renderResponse() should call sub-sequent engine renderResponse() method on template with same name but engine replaced to configured one.');
        $this->assertEquals([], $templating->lastParameters, 'ConfigEngine::renderResponse() should pass empty array as parameters by default to sub-sequent engine renderResponse() method.');
        $this->assertNull($templating->lastResponse, 'ConfigEngine::renderResponse() should pass null as default response object to sub-sequent engine renderResponse() method.');
        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\Response', $return, 'ConfigEngine::renderResponse() should always return instance of type Symfony\\Component\\HttpFoundation\\Response.');
    }

    /**
     * @test
     * @expectedException LogicException
     * @expectedExceptionMessage Template "qux.config" cannot be streamed as the sub-sequent target engine "ChillDev\Bundle\FileManagerBundle\Tests\Templating\MockTemplatingEngine" configured to handle it does not implement StreamingEngineInterface.
     * @version 0.0.1
     * @since 0.0.1
     */
    public function streamWithoutSupport()
    {
        $templating = new MockTemplatingEngine();
        $this->container->set('templating', $templating);

        $this->templating->stream('qux.config');
    }

    /**
     * @test
     * @version 0.0.1
     * @since 0.0.1
     */
    public function streamSubsequentEngine()
    {
        $parameters = ['bar' => 'baz'];

        $templating = new MockStreamingTemplatingEngine();
        $this->container->set('templating', $templating);

        $return = $this->templating->stream('qux.config', $parameters);
        $this->assertEquals('stream', $templating->lastCall, 'ConfigEngine::stream() should call sub-sequent engine stream() method.');
        $this->assertEquals($this->engine, $templating->lastName->get('engine'), 'ConfigEngine::stream() should call sub-sequent engine stream() method on template with same name but engine replaced to configured one.');
        $this->assertEquals($parameters, $templating->lastParameters, 'ConfigEngine::stream() should pass all parameters to sub-sequent engine stream() method.');
    }

    /**
     * @test
     * @version 0.0.1
     * @since 0.0.1
     */
    public function streamDefaultParameters()
    {
        $templating = new MockStreamingTemplatingEngine();
        $this->container->set('templating', $templating);

        $this->templating->stream('qux.config');
        $this->assertEquals('stream', $templating->lastCall, 'ConfigEngine::stream() should call sub-sequent engine stream() method.');
        $this->assertEquals($this->engine, $templating->lastName->get('engine'), 'ConfigEngine::stream() should call sub-sequent engine stream() method on template with same name but engine replaced to configured one.');
        $this->assertEquals([], $templating->lastParameters, 'ConfigEngine::stream() should pass empty array as parameters by default to sub-sequent engine stream() method.');
    }
}

class MockTemplatingEngine implements EngineInterface
{
    public $lastCall;
    public $lastName;
    public $lastParameters;
    public $lastResponse;
    public $toReturn;

    public function render($name, array $parameters = [])
    {
        $this->lastCall = 'render';
        $this->lastName = $name;
        $this->lastParameters = $parameters;
        return $this->toReturn;
    }

    public function exists($name)
    {
        $this->lastCall = 'exists';
        $this->lastName = $name;
        $this->lastParameters = null;
        return $this->toReturn;
    }

    public function supports($name)
    {
        // not needed
    }

    public function renderResponse($view, array $parameters = [], Response $response = null)
    {
        $this->lastCall = 'renderResponse';
        $this->lastName = $view;
        $this->lastParameters = $parameters;
        $this->lastResponse = $response;
        return $response ?: new Response();
    }
}

class MockStreamingTemplatingEngine extends MockTemplatingEngine implements StreamingEngineInterface
{
    public function stream($name, array $parameters = [])
    {
        $this->lastCall = 'stream';
        $this->lastName = $name;
        $this->lastParameters = $parameters;
    }
}
