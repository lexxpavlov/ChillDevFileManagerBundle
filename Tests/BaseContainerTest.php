<?php

/**
 * This file is part of the ChillDev FileManager bundle.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.0
 * @since 0.0.2
 * @package ChillDev\Bundle\FileManagerBundle
 */

namespace ChillDev\Bundle\FileManagerBundle\Tests;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpKernel\DependencyInjection\MergeExtensionConfigurationPass;

/**
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.0
 * @since 0.0.2
 * @package ChillDev\Bundle\FileManagerBundle
 */
abstract class BaseContainerTest extends BaseManagerTest
{
    /**
     * DI container.
     *
     * @var ContainerBuilder
     * @version 0.0.2
     * @since 0.0.2
     */
    protected $container;

    /**
     * @version 0.1.0
     * @since 0.0.2
     */
    protected function setUp()
    {
        parent::setUp();

        $bundle = new FrameworkBundle();
        $extension = $bundle->getContainerExtension();

        // build container with FrameworkBundle services
        $this->container = new ContainerBuilder(new ParameterBag([
                    'kernel.bundles' => ['FrameworkBundle' => 'Symfony\\Bundle\\FrameworkBundle\\FrameworkBundle'],
                    'kernel.cache_dir' => __DIR__,
                    'kernel.charset' => 'utf-8',
                    'kernel.debug' => false,
                    'kernel.root_dir' => __DIR__,
        ]));
        $this->container->registerExtension($extension);
        $bundle->build($this->container);
        $this->container->loadFromExtension('framework', [
                'secret' => 'secret',
                'validation' => true,
                'form' => true,
                'csrf_protection' => false,
        ]);
        $this->setUpContainer();
        $this->container->compile();
    }

    /**
     * @version 0.0.2
     * @since 0.0.2
     */
    protected function setUpContainer()
    {
    }
}
