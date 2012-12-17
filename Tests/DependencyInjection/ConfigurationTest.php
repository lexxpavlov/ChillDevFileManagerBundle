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

namespace ChillDev\Bundle\FileManagerBundle\Tests\DependencyInjection;

use PHPUnit_Framework_TestCase;

use ChillDev\Bundle\FileManagerBundle\DependencyInjection\ChillDevFileManagerExtension;
use ChillDev\Bundle\FileManagerBundle\DependencyInjection\Configuration;

use Symfony\Component\Config\Definition\NodeInterface;

/**
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.1
 * @since 0.0.1
 * @package ChillDev\Bundle\FileManagerBundle
 */
class ConfigurationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var NodeInterface
     * @version 0.0.1
     * @since 0.0.1
     */
    protected $tree;

    /**
     * @version 0.0.1
     * @since 0.0.1
     */
    protected function setUp()
    {
        $this->tree = (new Configuration())->getConfigTreeBuilder()->buildTree();
    }

    /**
     * Check if root node name matches extension alias.
     *
     * @test
     * @version 0.0.1
     * @since 0.0.1
     */
    public function rootNodeName()
    {
        $extension = new ChillDevFileManagerExtension();

        $this->assertEquals($extension->getAlias(), $this->tree->getName(), 'Configuretion::getConfigTreeBuilder() should return node matching bundle\'s extension alias.');
    }

    /**
     * Check multiple disks handling.
     *
     * @test
     * @version 0.0.1
     * @since 0.0.1
     */
    public function multipleDisksDefinition()
    {
        $label1 = 'foo';
        $source1 = 'bar';
        $label2 = 'baz';
        $source2 = 'qux';

        $config = $this->tree->finalize($this->tree->normalize([
                    'disks' => [
                        'test1' => [
                            'label' => $label1,
                            'source' => $source1,
                        ],
                        'test2' => [
                            'label' => $label2,
                            'source' => $source2,
                        ],
                    ],
        ]));

        $this->assertEquals($label1, $config['disks']['test1']['label'], 'Configuration should handle key disks.$n.label for each link definition.');
        $this->assertEquals($source1, $config['disks']['test1']['source'], 'Configuration should handle key disks.$n.source for each link definition.');
        $this->assertEquals($label2, $config['disks']['test2']['label'], 'Configuration should handle key disks.$n.label for each link definition.');
        $this->assertEquals($source2, $config['disks']['test2']['source'], 'Configuration should handle key disks.$n.source for each link definition.');
    }

    /**
     * Check requirement constraint on "label" property of disk definition.
     *
     * @test
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "label" at path "chilldev_filemanager.disks.test" must be configured.
     * @version 0.0.1
     * @since 0.0.1
     */
    public function requiredDiskLabel()
    {
        $config = $this->tree->finalize($this->tree->normalize([
                    'disks' => [
                        'test' => [
                            'source' => 'foo',
                        ],
                    ],
        ]));
    }

    /**
     * Check requirement constraint on "source" property of disk definition.
     *
     * @test
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "source" at path "chilldev_filemanager.disks.test" must be configured.
     * @version 0.0.1
     * @since 0.0.1
     */
    public function requiredDiskSource()
    {
        $config = $this->tree->finalize($this->tree->normalize([
                    'disks' => [
                        'test' => [
                            'label' => 'foo',
                        ],
                    ],
        ]));
    }
}
