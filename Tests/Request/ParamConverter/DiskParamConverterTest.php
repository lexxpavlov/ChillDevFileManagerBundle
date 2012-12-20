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

namespace ChillDev\Bundle\FileManagerBundle\Tests\Request\ParamConverter;

use PHPUnit_Framework_TestCase;

use ChillDev\Bundle\FileManagerBundle\Filesystem\Manager;
use ChillDev\Bundle\FileManagerBundle\Request\ParamConverter\DiskParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Symfony\Component\HttpFoundation\Request;

/**
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.1
 * @since 0.0.1
 * @package ChillDev\Bundle\FileManagerBundle
 */
class DiskParamConverterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Manager
     * @version 0.0.1
     * @since 0.0.1
     */
    protected $manager;

    /**
     * @var DiskParamConverter
     * @version 0.0.1
     * @since 0.0.1
     */
    protected $converter;

    /**
     * @version 0.0.1
     * @since 0.0.1
     */
    protected function setUp()
    {
        $this->manager = new Manager();
        $this->manager->createDisk('id', 'test', 'foo');

        $this->converter = new DiskParamConverter($this->manager);
    }

    /**
     * @test
     * @version 0.0.1
     * @since 0.0.1
     */
    public function supportedClass()
    {
        $configuration = new ParamConverter([]);

        $configuration->setClass('ChillDev\\Bundle\\FileManagerBundle\\Filesystem\\Disk');
        $this->assertTrue($this->converter->supports($configuration), 'DiskParamConverter::supports() should return true for handling class "ChillDev\\Bundle\\FileManagerBundle\\Filesystem\\Disk".');

        $configuration->setClass('ChillDev\\Bundle\\FileManagerBundle\\Filesystem\\Manager');
        $this->assertFalse($this->converter->supports($configuration), 'DiskParamConverter::supports() should return false for handling class different then "ChillDev\\Bundle\\FileManagerBundle\\Filesystem\\Disk".');
    }

    /**
     * @test
     * @version 0.0.1
     * @since 0.0.1
     */
    public function defaultParameterName()
    {
        $id = 'id';
        $param = 'disk';
        $request = Request::create('/');
        $request->attributes->set($param, $id);

        $configuration = new ParamConverter([]);
        $configuration->setClass('ChillDev\\Bundle\\FileManagerBundle\\Filesystem\\Disk');
        $configuration->setName('disk');

        $return = $this->converter->apply($request, $configuration);
        $this->assertSame($this->manager[$id], $request->attributes->get($param), 'DiskParamConverter::apply() should set attribute with same name as input parameter as disk reference taken from manager.');
        $this->assertTrue($return, 'DiskParamConverter::apply() should mark conversion to be done.');
    }

    /**
     * @test
     * @version 0.0.1
     * @since 0.0.1
     */
    public function customParameterName()
    {
        $id = 'id';
        $param = 'disk';
        $argument = 'foo';
        $request = Request::create('/');
        $request->attributes->set($param, $id);

        $configuration = new ParamConverter([]);
        $configuration->setClass('ChillDev\\Bundle\\FileManagerBundle\\Filesystem\\Disk');
        $configuration->setName($argument);
        $configuration->setOptions(['param' => $param]);

        $return = $this->converter->apply($request, $configuration);
        $this->assertSame($this->manager[$id], $request->attributes->get($argument), 'DiskParamConverter::apply() should set attribute with specified name as disk reference taken from manager.');
        $this->assertTrue($return, 'DiskParamConverter::apply() should mark conversion to be done.');
    }

    /**
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage Disk specified by request as "foo" is not configured.
     * @version 0.0.1
     * @since 0.0.1
     */
    public function notExistingDisk()
    {
        $param = 'disk';
        $request = Request::create('/');
        $request->attributes->set($param, 'foo');

        $configuration = new ParamConverter([]);
        $configuration->setClass('ChillDev\\Bundle\\FileManagerBundle\\Filesystem\\Disk');
        $configuration->setName($param);

        $this->converter->apply($request, $configuration);
    }
}
