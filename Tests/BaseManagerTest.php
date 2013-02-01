<?php

/**
 * This file is part of the ChillDev FileManager bundle.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.2
 * @since 0.0.2
 * @package ChillDev\Bundle\FileManagerBundle
 */

namespace ChillDev\Bundle\FileManagerBundle\Tests;

use PHPUnit_Framework_TestCase;

use ChillDev\Bundle\FileManagerBundle\Filesystem\Manager;

use org\bovigo\vfs\vfsStream;

/**
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.2
 * @since 0.0.2
 * @package ChillDev\Bundle\FileManagerBundle
 */
abstract class BaseManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     * @version 0.0.2
     * @since 0.0.2
     */
    const ROOT_DIR = 'root';

    /**
     * Disks manager.
     *
     * @var Manager
     * @version 0.0.2
     * @since 0.0.2
     */
    protected $manager;

    /**
     * @version 0.0.2
     * @since 0.0.2
     */
    protected function setUp()
    {
        vfsStream::setup(self::ROOT_DIR);

        $this->manager = new Manager();
        $this->manager->createDisk('id', 'Test', vfsStream::url(self::ROOT_DIR) . '/');
    }
}
