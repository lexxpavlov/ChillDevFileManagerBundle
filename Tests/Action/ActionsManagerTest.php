<?php

/**
 * This file is part of the ChillDev FileManager bundle.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2014 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.3
 * @since 0.1.3
 * @package ChillDev\Bundle\FileManagerBundle
 */

namespace ChillDev\Bundle\FileManagerBundle\Tests\Action;

use PHPUnit_Framework_TestCase;

use ChillDev\Bundle\FileManagerBundle\Action\ActionsManager;

/**
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2014 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.3
 * @since 0.1.3
 * @package ChillDev\Bundle\FileManagerBundle
 */
class ActionsManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @version 0.1.3
     * @since 0.1.3
     */
    public function registerHandler()
    {
        $handler = $this->getMock(
            'ChillDev\\Bundle\\FileManagerBundle\\Action\\Handler\\HandlerInterface',
            ['supports', 'getLabel', 'handle']
        );
        $action = 'test';

        $manager = new ActionsManager();
        $return = $manager->registerHandler($action, $handler);

        $this->assertTrue(isset($manager[$action]), 'ActionsManager::registerHandler() should put handler definition under key of it\'s action.');
        $this->assertSame($manager, $return, 'ActionsManager::registerHandler() should return reference to itself.');
    }

    /**
     * @test
     * @version 0.1.3
     * @since 0.1.3
     */
    public function getActionsForType()
    {
        $mimeType = 'test';

        $handler1 = $this->getMock(
            'ChillDev\\Bundle\\FileManagerBundle\\Action\\Handler\\HandlerInterface',
            ['supports', 'getLabel', 'handle']
        );
        $action1 = 'foo';
        $handler1->expects($this->once())
            ->method('supports')
            ->with($this->equalTo($mimeType))
            ->will($this->returnValue(true));

        $handler2 = $this->getMock(
            'ChillDev\\Bundle\\FileManagerBundle\\Action\\Handler\\HandlerInterface',
            ['supports', 'getLabel', 'handle']
        );
        $action2 = 'bar';
        $handler2->expects($this->once())
            ->method('supports')
            ->with($this->equalTo($mimeType))
            ->will($this->returnValue(false));

        $manager = new ActionsManager();
        $manager->registerHandler($action1, $handler1)
            ->registerHandler($action2, $handler2);

        $actions = $manager->getActionsForType($mimeType);
        $this->assertContains($handler1, $actions, 'ActionsManager::getActionsForType() should return list of actions that support given MIME type.');
        $this->assertNotContains($handler2, $actions, 'ActionsManager::getActionsForType() should return list that doesn\'t contain actions that don\'t support given MIME type.');
    }
}
