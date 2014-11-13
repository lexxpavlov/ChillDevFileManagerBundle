<?php

/**
 * This file is part of the ChillDev FileManager bundle.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012 - 2014 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.4
 * @since 0.0.1
 * @package ChillDev\Bundle\FileManagerBundle
 */

namespace ChillDev\Bundle\FileManagerBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * ChillDev FileManager extensions.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012 - 2014 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.4
 * @since 0.0.1
 * @package ChillDev\Bundle\FileManagerBundle
 */
class ChillDevFileManagerExtension extends Extension
{
    /**
     * {@inheritDoc}
     * @version 0.1.4
     * @since 0.0.1
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        // disks services
        $loader->load('services.xml');

        if ($config['sonata_block']) {
            $loader->load('block.xml');
        }

        // SensioFrameworkExtraBundle pre-3.0
        if (
            !class_exists('Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\ParamConverter')
            && class_exists('Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\ConfigurationInterface')
        ) {
            $container->setParameter(
                'chilldev.filemanager.param_converter.disk.class',
                'ChillDev\\Bundle\\FileManagerBundle\\Request\\ParamConverter\\LegacyDiskParamConverter'
            );
            $container->setParameter(
                'chilldev.filemanager.param_converter.action_handler.class',
                'ChillDev\\Bundle\\FileManagerBundle\\Request\\ParamConverter\\LegacyActionHandlerParamConverter'
            );
        }

        // disks definitions
        $this->setInitialDisks($container, $config);
    }

    /**
     * {@inheritdoc}
     * @version 0.0.1
     * @since 0.0.1
     */
    public function getAlias()
    {
        return 'chilldev_filemanager';
    }

    /**
     * Handles disks configuration.
     *
     * @param ContainerBuilder $container DI container builder.
     * @param array $config Extension configuration.
     * @return self Self instance.
     * @version 0.1.1
     * @since 0.1.1
     */
    protected function setInitialDisks(ContainerBuilder $container, array $config)
    {
        $manager = $container->getDefinition('chilldev.filemanager.disks.manager');
        foreach ($config['disks'] as $id => $disk) {
            $manager->addMethodCall('createDisk', [$id, $disk['label'], $disk['source']]);
        }

        return $this;
    }
}
