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

namespace ChillDev\Bundle\FileManagerBundle\Tests\Form\Type;

use ChillDev\Bundle\FileManagerBundle\Form\Type\UploadType;
use ChillDev\Bundle\FileManagerBundle\Tests\BaseContainerTest;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.3
 * @since 0.0.3
 * @package ChillDev\Bundle\FileManagerBundle
 */
class UploadTypeTest extends BaseContainerTest
{
    /**
     * @test
     * @version 0.0.3
     * @since 0.0.3
     */
    public function uploadFormType()
    {
        $type = new UploadType($this->getMock('ChillDev\\Bundle\\FileManagerBundle\\Filesystem\\Filesystem', [], [], '', false), '');

        $this->assertEquals('upload', $type->getName(), 'UploadType::getName() should return "upload" as form scope.');

        // check form structure
        $builder = $this->getMock('Symfony\\Component\\Form\\FormBuilder', [], [], '', false);
        $builder->expects($this->once())
            ->method('addEventSubscriber')
            ->with($this->isInstanceOf('ChillDev\\Bundle\\FileManagerBundle\\Form\\EventListener\\UploadNameSubscriber'))
            ->will($this->returnSelf());
        $builder->expects($this->at(1))
            ->method('add')
            ->with($this->equalTo('file'), $this->equalTo('file'))
            ->will($this->returnSelf());
        $builder->expects($this->at(2))
            ->method('add')
            ->with($this->equalTo('name'), $this->equalTo('text'))
            ->will($this->returnSelf());
        $builder->expects($this->at(3))
            ->method('add')
            ->with($this->equalTo('force'), $this->equalTo('checkbox'))
            ->will($this->returnSelf());

        $type->buildForm($builder, []);
    }

    /**
     * @test
     * @version 0.0.3
     * @since 0.0.3
     */
    public function uploadNameValidator()
    {
        $filesystem = $this->getMock('ChillDev\\Bundle\\FileManagerBundle\\Filesystem\\Filesystem', [], [], '', false);

        $type = new UploadType($filesystem, '');

        $options = new OptionsResolver();
        $type->setDefaultOptions($options);
        $constraints = $options->resolve()['constraints'];
        $constraints->fields = ['name' => $constraints->fields['name']];

        // create value validator
        $validator = $this->container->get('validator');

        // test values
        $this->assertCount(0, $validator->validateValue(['name' => 'foobar'], $constraints), 'UploadType::setDefaultOptions() should set validators that accept proper filename.');
        $this->assertNotCount(0, $validator->validateValue(['name' => '.'], $constraints), 'UploadType::setDefaultOptions() should set validators that reject "." filename.');
        $this->assertNotCount(0, $validator->validateValue(['name' => '..'], $constraints), 'UploadType::setDefaultOptions() should set validators that reject ".." filename.');
        $this->assertNotCount(0, $validator->validateValue(['name' => 'l/ll'], $constraints), 'UploadType::setDefaultOptions() should set validators that reject invalid filename.');
        $this->assertNotCount(0, $validator->validateValue(['name' => \chr(0)], $constraints), 'UploadType::setDefaultOptions() should set validators that reject filename with null character.');
    }
}
