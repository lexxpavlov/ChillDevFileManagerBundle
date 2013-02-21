<?php

/**
 * This file is part of the ChillDev FileManager bundle.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012 - 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.3
 * @since 0.0.1
 * @package ChillDev\Bundle\FileManagerBundle
 */

namespace ChillDev\Bundle\FileManagerBundle\Tests\Form\Type;

use ChillDev\Bundle\FileManagerBundle\Form\Type\MkdirType;
use ChillDev\Bundle\FileManagerBundle\Tests\BaseContainerTest;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012 - 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.2
 * @since 0.0.1
 * @package ChillDev\Bundle\FileManagerBundle
 */
class MkdirTypeTest extends BaseContainerTest
{
    /**
     * @test
     * @version 0.0.2
     * @since 0.0.1
     */
    public function mkdirFormType()
    {
        $type = new MkdirType($this->getMock('ChillDev\\Bundle\\FileManagerBundle\\Filesystem\\Filesystem', [], [], '', false), '');

        $this->assertEquals('mkdir', $type->getName(), 'MkdirType::getName() should return "mkdir" as form scope.');

        // check form structure
        $builder = $this->getMock('Symfony\\Component\\Form\\FormBuilder', [], [], '', false);
        $builder->expects($this->once())
            ->method('add')
            ->with($this->equalTo('name'), $this->equalTo('text'))
            ->will($this->returnSelf());

        $type->buildForm($builder, []);
    }

    /**
     * @test
     * @version 0.0.2
     * @since 0.0.1
     */
    public function mkdirNameValidator()
    {
        $filesystem = $this->getMock('ChillDev\\Bundle\\FileManagerBundle\\Filesystem\\Filesystem', [], [], '', false);
        $filesystem->expects($this->at(0))
            ->method('exists')
            ->will($this->returnValue(false));
        $filesystem->expects($this->at(1))
            ->method('exists')
            ->will($this->returnValue(false));
        $filesystem->expects($this->at(2))
            ->method('exists')
            ->will($this->returnValue(false));
        $filesystem->expects($this->at(3))
            ->method('exists')
            ->will($this->returnValue(false));
        $filesystem->expects($this->at(4))
            ->method('exists')
            ->will($this->returnValue(false));
        $filesystem->expects($this->at(5))
            ->method('exists')
            ->will($this->returnValue(true));

        $type = new MkdirType($filesystem, '');

        $options = new OptionsResolver();
        $type->setDefaultOptions($options);
        $constraints = $options->resolve()['constraints'];
        $constraints->fields = ['name' => $constraints->fields['name']];

        // create value validator
        $validator = $this->container->get('validator');

        // test values
        $this->assertCount(0, $validator->validateValue(['name' => 'foobar'], $constraints), 'MkdirType::setDefaultOptions() should set validators that accept proper filename.');
        $this->assertNotCount(0, $validator->validateValue(['name' => ''], $constraints), 'MkdirType::setDefaultOptions() should set validators that reject empty filename.');
        $this->assertNotCount(0, $validator->validateValue(['name' => '.'], $constraints), 'MkdirType::setDefaultOptions() should set validators that reject "." filename.');
        $this->assertNotCount(0, $validator->validateValue(['name' => '..'], $constraints), 'MkdirType::setDefaultOptions() should set validators that reject ".." filename.');
        $this->assertNotCount(0, $validator->validateValue(['name' => 'l/ll'], $constraints), 'MkdirType::setDefaultOptions() should set validators that reject invalid filename.');
        $this->assertNotCount(0, $validator->validateValue(['name' => \chr(0)], $constraints), 'MkdirType::setDefaultOptions() should set validators that reject filename with null character.');
        $this->assertNotCount(0, $validator->validateValue(['name' => 'foo'], $constraints), 'MkdirType::setDefaultOptions() should set validators that reject existing filenames.');
    }
}
