<?php

/**
 * This file is part of the ChillDev FileManager bundle.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012, 2014 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.3
 * @since 0.0.1
 * @package ChillDev\Bundle\FileManagerBundle
 */

namespace ChillDev\Bundle\FileManagerBundle\Tests;

use PHPUnit_Framework_TestCase;

use ChillDev\Bundle\FileManagerBundle\ChillDevFileManagerBundle;
use ChillDev\Bundle\FileManagerBundle\DependencyInjection\ChillDevFileManagerExtension;

/**
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012, 2014 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.3
 * @since 0.0.1
 * @package ChillDev\Bundle\FileManagerBundle
 */
class BundleTest extends PHPUnit_Framework_TestCase
{
    /**
     * Check if bundle registers own extension.
     *
     * @test
     * @version 0.0.1
     * @since 0.0.1
     */
    public function ownExtension()
    {
        $extension = (new ChillDevFileManagerBundle())->getContainerExtension();
        $this->assertEquals('chilldev_filemanager', $extension->getAlias(), 'ChillDevFileManagerBundle::getContainerExtension() should return bundle\'s extension.');
    }

    /**
     * @test
     * @version 0.1.3
     * @since 0.1.3
     */
    public function build()
    {
        $mock = $this->getMock('Symfony\\Component\\DependencyInjection\\ContainerBuilder', ['addCompilerPass']);
        $mock->expects($this->once())
            ->method('addCompilerPass')
            ->with($this->isInstanceOf('ChillDev\\DependencyInjection\\Compiler\\TagGrabbingPass'));

        (new ChillDevFileManagerBundle())->build($mock);
    }
}
