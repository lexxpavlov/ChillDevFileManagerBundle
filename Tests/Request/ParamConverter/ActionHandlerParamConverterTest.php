<?php

/**
 * This file is part of the ChillDev FileManager bundle.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2014 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.4
 * @since 0.1.4
 * @package ChillDev\Bundle\FileManagerBundle
 */

namespace ChillDev\Bundle\FileManagerBundle\Tests\Request\ParamConverter;

use PHPUnit_Framework_TestCase;

use ChillDev\Bundle\FileManagerBundle\Action\ActionsManager;
use ChillDev\Bundle\FileManagerBundle\Request\ParamConverter\ActionHandlerParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Symfony\Component\HttpFoundation\Request;

/**
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2014 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.4
 * @since 0.1.4
 * @package ChillDev\Bundle\FileManagerBundle
 */
class ActionHandlerParamConverterTest extends PHPUnit_Framework_TestCase
{
    /**
     * Actions handlers manager.
     *
     * @var ActionsManager
     * @version 0.1.4
     * @since 0.1.4
     */
    protected $manager;

    /**
     * @var ActionHandlerParamConverter
     * @version 0.1.4
     * @since 0.1.4
     */
    protected $converter;

    /**
     * @version 0.1.4
     * @since 0.1.4
     */
    protected function setUp()
    {
        $this->manager = new ActionsManager();
        $this->manager->registerHandler('test', $this->getMock('ChillDev\\Bundle\\FileManagerBundle\\Action\\Handler\\HandlerInterface'));

        $this->converter = new ActionHandlerParamConverter($this->manager);
    }

    /**
     * @test
     * @version 0.1.4
     * @since 0.1.4
     */
    public function supportedClass()
    {
        $configuration = new ParamConverter([]);

        $configuration->setClass('ChillDev\\Bundle\\FileManagerBundle\\Action\\Handler\\HandlerInterface');
        $this->assertTrue($this->converter->supports($configuration), 'ActionHandlerParamConverter::supports() should return true for handling class "ChillDev\\Bundle\\FileManagerBundle\\Action\\Handler\\HandlerInterface".');

        $configuration->setClass('ChillDev\\Bundle\\FileManagerBundle\\Action\\ActionsManager');
        $this->assertFalse($this->converter->supports($configuration), 'ActionHandlerParamConverter::supports() should return false for handling class different than "ChillDev\\Bundle\\FileManagerBundle\\Action\\Handler\\HandlerInterface".');
    }

    /**
     * @test
     * @version 0.1.4
     * @since 0.1.4
     */
    public function defaultParameterName()
    {
        $id = 'test';
        $param = 'action';
        $request = Request::create('/');
        $request->attributes->set($param, $id);

        $configuration = new ParamConverter([]);
        $configuration->setClass('ChillDev\\Bundle\\FileManagerBundle\\Action\\Handler\\HandlerInterface');
        $configuration->setName('action');

        $return = $this->converter->apply($request, $configuration);
        $this->assertSame($this->manager[$id], $request->attributes->get($param), 'ActionHandlerParamConverter::apply() should set attribute with same name as input parameter as action handle reference taken from manager.');
        $this->assertTrue($return, 'ActionHandlerParamConverter::apply() should mark conversion to be done.');
    }

    /**
     * @test
     * @version 0.1.4
     * @since 0.1.4
     */
    public function customParameterName()
    {
        $id = 'test';
        $param = 'action';
        $argument = 'foo';
        $request = Request::create('/');
        $request->attributes->set($param, $id);

        $configuration = new ParamConverter([]);
        $configuration->setClass('ChillDev\\Bundle\\FileManagerBundle\\Action\\Handler\\HandlerInterface');
        $configuration->setName($argument);
        $configuration->setOptions(['param' => $param]);

        $return = $this->converter->apply($request, $configuration);
        $this->assertSame($this->manager[$id], $request->attributes->get($argument), 'ActionHandlerParamConverter::apply() should set attribute with specified name as action handler reference taken from manager.');
        $this->assertTrue($return, 'ActionHandlerParamConverter::apply() should mark conversion to be done.');
    }

    /**
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage Action specified by request as "foo" has no handler registred.
     * @version 0.1.4
     * @since 0.1.4
     */
    public function notExistingActionHandler()
    {
        $param = 'action';
        $request = Request::create('/');
        $request->attributes->set($param, 'foo');

        $configuration = new ParamConverter([]);
        $configuration->setClass('ChillDev\\Bundle\\FileManagerBundle\\Action\\Handler\\HandlerInterface');
        $configuration->setName($param);

        $this->converter->apply($request, $configuration);
    }
}
