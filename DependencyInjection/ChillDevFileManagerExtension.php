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

namespace ChillDev\Bundle\FileManagerBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * ChillDev FileManager extensions.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012 - 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.1
 * @since 0.0.1
 * @package ChillDev\Bundle\FileManagerBundle
 */
class ChillDevFileManagerExtension extends Extension
{
    /**
     * {@inheritDoc}
     * @version 0.1.1
     * @since 0.0.1
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        // disks services
        $loader->load('services.xml');

        // disks definitions
        $manager = $container->getDefinition('chilldev.filemanager.disks.manager');
        foreach ($config['disks'] as $id => $disk) {
            $manager->addMethodCall('createDisk', [$id, $disk['label'], $disk['source']]);
        }
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
}
