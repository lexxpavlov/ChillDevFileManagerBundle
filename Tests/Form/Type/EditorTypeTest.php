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

namespace ChillDev\Bundle\FileManagerBundle\Tests\Form\Type;

use PHPUnit_Framework_TestCase;

use ChillDev\Bundle\FileManagerBundle\Form\Type\EditorType;

/**
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2014 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.3
 * @since 0.1.3
 * @package ChillDev\Bundle\FileManagerBundle
 */
class EditorTypeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @version 0.1.3
     * @since 0.1.3
     */
    public function editorFormType()
    {
        $type = new EditorType();

        $this->assertEquals('action_edit', $type->getName(), 'EditorType::getName() should return "action_edit" as form scope.');

        // check form structure
        $builder = $this->getMock('Symfony\\Component\\Form\\FormBuilder', [], [], '', false);
        $builder->expects($this->once())
            ->method('add')
            ->with($this->equalTo('content'), $this->equalTo('textarea'))
            ->will($this->returnSelf());

        $type->buildForm($builder, []);
    }
}
