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

namespace ChillDev\Bundle\FileManagerBundle\Tests\Filesystem;

use PHPUnit_Framework_TestCase;

use ChillDev\Bundle\FileManagerBundle\Filesystem\Disk;
use ChillDev\Bundle\FileManagerBundle\Filesystem\Manager;

/**
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.1
 * @since 0.0.1
 * @package ChillDev\Bundle\FileManagerBundle
 */
class ManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * Check if disk definition is created correctly.
     *
     * @test
     * @version 0.0.1
     * @since 0.0.1
     */
    public function diskCreation()
    {
        $id = 'foo';
        $label = 'bar';
        $source = 'baz';

        $manager = new Manager();
        $return = $manager->createDisk($id, $label, $source);

        $this->assertTrue(isset($manager[$id]), 'Manager::createDisk() should put disk definition under key of it\'s id.');

        $disk = $manager[$id];

        $this->assertInstanceOf('ChillDev\\Bundle\\FileManagerBundle\\Filesystem\\Disk', $disk, 'Manager::createDisk() should create disk definition of type ChillDev\\Bundle\\FileManagerBundle\\Filesystem\\Disk.');
        $this->assertEquals($id, $disk->getId(), 'Manager::createDisk() should set disk identifier.');
        $this->assertEquals($label, $disk->getLabel(), 'Manager::createDisk() should set disk label.');
        $this->assertEquals($source, $disk->getSource(), 'Manager::createDisk() should set disk source.');
        $this->assertSame($manager, $return, 'Manager::createDisk() should return reference to itself.');
    }
}
