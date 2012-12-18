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

namespace ChillDev\Bundle\FileManagerBundle\Tests\Controller;

use PHPUnit_Framework_TestCase;

use ChillDev\Bundle\FileManagerBundle\Controller\DisksController;
use ChillDev\Bundle\FileManagerBundle\Filesystem\Disk;
use ChillDev\Bundle\FileManagerBundle\Filesystem\Manager;

use Symfony\Component\DependencyInjection\Container;

/**
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.1
 * @since 0.0.1
 * @package ChillDev\Bundle\FileManagerBundle
 */
class DisksControllerTest extends PHPUnit_Framework_TestCase
{
    /**
     * DI container.
     *
     * @var Container
     * @version 0.0.1
     * @since 0.0.1
     */
    protected $container;

    /**
     * Disks manager.
     *
     * @var Manager
     * @version 0.0.1
     * @since 0.0.1
     */
    protected $manager;

    /**
     * @version 0.0.1
     * @since 0.0.1
     */
    protected function setUp()
    {
        $this->container = new Container();
        $this->manager = new Manager();

        $this->container->set('chilldev.filemanager.disks.manager', $this->manager);
    }

    /**
     * Check list action.
     *
     * @test
     * @version 0.0.1
     * @since 0.0.1
     */
    public function listAction()
    {
        $controller = new DisksController();
        $controller->setContainer($this->container);

        $this->assertSame($this->manager, $controller->listAction()['disks'], 'DisksController::listAction() should return disks manager under key "disks".');
    }
}
