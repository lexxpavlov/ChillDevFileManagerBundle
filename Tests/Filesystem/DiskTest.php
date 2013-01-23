<?php

/**
 * This file is part of the ChillDev FileManager bundle.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012 - 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.2
 * @since 0.0.1
 * @package ChillDev\Bundle\FileManagerBundle
 */

namespace ChillDev\Bundle\FileManagerBundle\Tests\Filesystem;

use PHPUnit_Framework_TestCase;

use ChillDev\Bundle\FileManagerBundle\Filesystem\Disk;
use ChillDev\Bundle\FileManagerBundle\Tests\BaseManagerTest;

/**
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012 - 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.2
 * @since 0.0.1
 * @package ChillDev\Bundle\FileManagerBundle
 */
class DiskTest extends BaseManagerTest
{
    /**
     * Check if constructor arguments are remembered.
     *
     * @test
     * @version 0.0.1
     * @since 0.0.1
     */
    public function parametersFromConstructor()
    {
        $id = 'foo';
        $label = 'bar';
        $source = 'baz';

        $disk = new Disk($id, $label, $source);

        $this->assertEquals($id, $disk->getId(), 'Disk::__construct() should set id passed as argument.');
        $this->assertEquals($label, $disk->getLabel(), 'Disk::__construct() should set label passed as argument.');
        $this->assertEquals($source, $disk->getSource(), 'Disk::__construct() should set source passed as argument.');
    }

    /**
     * Check to-string conversion.
     *
     * @test
     * @version 0.0.1
     * @since 0.0.1
     */
    public function toStringConversion()
    {
        $label = 'foo';

        $disk = new Disk('', $label, '');
        $this->assertEquals('[' . $label . ']', $disk->__toString(), 'Disk::__toString() should generate printable disk label.');
    }

    /**
     * Check to-string casting.
     *
     * @test
     * @version 0.0.1
     * @since 0.0.1
     */
    public function toStringCasting()
    {
        $label = 'foo';

        $disk = new Disk('', $label, '');
        $this->assertEquals('[' . $label . ']', (string) $disk->__toString(), 'Disk::__toString() should handle conversion to string.');
    }

    /**
     * @test
     * @version 0.0.2
     * @since 0.0.2
     */
    public function filesystemCreation()
    {
        $this->assertInstanceOf('ChillDev\\Bundle\\FileManagerBundle\\Filesystem\\Filesystem', $this->manager['id']->getFilesystem(), 'Disk::getFilesystem() should return filesystem instance.');
    }
}
