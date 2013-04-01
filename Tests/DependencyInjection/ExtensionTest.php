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

namespace ChillDev\Bundle\FileManagerBundle\Tests\DependencyInjection;

use PHPUnit_Framework_TestCase;

use ChillDev\Bundle\FileManagerBundle\DependencyInjection\ChillDevFileManagerExtension;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012 - 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.1
 * @since 0.0.1
 * @package ChillDev\Bundle\FileManagerBundle
 */
class ExtensionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ChillDevFileManagerExtension
     * @version 0.0.1
     * @since 0.0.1
     */
    protected $extension;

    /**
     * @version 0.0.1
     * @since 0.0.1
     */
    protected function setUp()
    {
        $this->extension = new ChillDevFileManagerExtension();
    }

    /**
     * Check if disks parameters are handled correctly.
     *
     * @test
     * @version 0.0.1
     * @since 0.0.1
     */
    public function definedDisks()
    {
        $id = 'foo';
        $label = 'bar';
        $source = 'baz';

        $config = [
            'disks' => [
                $id => [
                    'label' => $label,
                    'source' => $source,
                ],
            ],
        ];
        $container = new ContainerBuilder();

        $this->extension->load([$config], $container);

        foreach ($container->getDefinition('chilldev.filemanager.disks.manager')->getMethodCalls() as $call) {
            if ($call[0] === 'createDisk') {
                $this->assertEquals($id, $call[1][0], 'ChillDevFileManagerExtension::load() should set id parameter for "chilldev.filemanager.disks.manager"::createDisk().');
                $this->assertEquals($label, $call[1][1], 'ChillDevFileManagerExtension::load() should set label parameter for "chilldev.filemanager.disks.manager"::createDisk().');
                $this->assertEquals($source, $call[1][2], 'ChillDevFileManagerExtension::load() should set source parameter for "chilldev.filemanager.disks.manager"::createDisk().');
                return;
            }
        }

        $this->fail('ChillDevFileManagerExtension::load() should set defined disks in "chilldev.filemanager.disks.manager" service definition.');
    }
}
