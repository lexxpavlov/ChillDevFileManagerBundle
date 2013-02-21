<?php

/**
 * This file is part of the ChillDev FileManager bundle.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.3
 * @since 0.0.3
 * @package ChillDev\Bundle\FileManagerBundle
 */

namespace ChillDev\Bundle\FileManagerBundle\Tests\Form\EventListener;

use PHPUnit_Framework_TestCase;

use ChillDev\Bundle\FileManagerBundle\Form\EventListener\UploadNameSubscriber;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Collection;

/**
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.3
 * @since 0.0.3
 * @package ChillDev\Bundle\FileManagerBundle
 */
class UploadNameSubscriberTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @version 0.0.3
     * @since 0.0.3
     */
    public function subscribedToPreBind()
    {
        $this->assertArrayHasKey(FormEvents::PRE_BIND, UploadNameSubscriber::getSubscribedEvents(), 'UploadNameSubscriber::getSubscribedEvents() should provide callback for FormEvents::PRE_BIND event.');
    }

    /**
     * @test
     * @version 0.0.3
     * @since 0.0.3
     */
    public function preBindDefaultName()
    {
        $listener = new UploadNameSubscriber($this->getMock('Symfony\\Component\\Validator\\Constraints\\Collection', [], [], '', false), $this->getMock('ChillDev\\Bundle\\FileManagerBundle\\Validator\\Constraints\\FileNotExists', [], [], '', false));

        $event = new FormEvent($this->getMock('Symfony\\Component\\Form\\Tests\\FormInterface'), null);
        $name = 'foo';
        $file = $this->getMock('Symfony\\Component\\HttpFoundation\\File\\UploadedFile', [], [], '', false);
        $file->expects($this->any())
            ->method('getClientOriginalName')
            ->will($this->returnValue($name));

        // keep defined name
        $filename = new \stdClass();
        $event->setData(['file' => $file, 'name' => $filename]);
        $listener->preBind($event);
        $this->assertSame($filename, $event->getData()['name'], 'UploadNameSubscriber::preBind() should leave specified name if it is set.');

        // set default name if defined is empty
        $event->setData(['file' => $file, 'name' => '']);
        $listener->preBind($event);
        $this->assertEquals($name, $event->getData()['name'], 'UploadNameSubscriber::preBind() should set uploaded filename as destination one if specified name is empty.');

        // set default name if defined is not set
        $event->setData(['file' => $file]);
        $listener->preBind($event);
        $this->assertEquals($name, $event->getData()['name'], 'UploadNameSubscriber::preBind() should set uploaded filename as destination one if it is not specified.');
    }

    /**
     * @test
     * @version 0.0.3
     * @since 0.0.3
     */
    public function preBindExistsCheck()
    {
        $check = $this->getMock('ChillDev\\Bundle\\FileManagerBundle\\Validator\\Constraints\\FileNotExists', [], [], '', false);
        $constraints = new Collection(['fields' => ['name' => []]]);
        $listener = new UploadNameSubscriber($constraints, $check);

        $event = new FormEvent($this->getMock('Symfony\\Component\\Form\\Tests\\FormInterface'), null);

        // don't add constraint
        $event->setData(['force' => true]);
        $listener->preBind($event);
        $this->assertNotContains($check, $constraints->fields['name']->constraints, 'UploadNameSubscriber::preBind() should not add file exist check if force flag is set.');

        // add existance check constraint
        $event->setData(['force' => false]);
        $listener->preBind($event);
        $this->assertContains($check, $constraints->fields['name']->constraints, 'UploadNameSubscriber::preBind() should add file exist check if force flag is not set.');
    }
}
