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

namespace ChillDev\Bundle\FileManagerBundle\Tests\Form\Type;

use Countable;
use IteratorAggregate;

use PHPUnit_Framework_TestCase;

use ChillDev\Bundle\FileManagerBundle\Form\Type\MkdirType;

use Symfony\Component\Form\FormBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\ConstraintValidatorFactory;
use Symfony\Component\Validator\Validator;
use Symfony\Component\Validator\Mapping\ClassMetadataFactory;

/**
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.1
 * @since 0.0.1
 * @package ChillDev\Bundle\FileManagerBundle
 */
class MkdirTypeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @version 0.0.1
     * @since 0.0.1
     */
    public function mkdirFormType()
    {
        $type = new MkdirType('');

        $this->assertEquals('mkdir', $type->getName(), 'MkdirType::getName() should return "mkdir" as form scope.');

        // check form structure
        $builder = new MockFormBuilder();
        $type->buildForm($builder, []);
        $this->assertCount(1, $builder->fields, 'MkdirType::buildForm() should define exactly 1 field.');
        $this->assertEquals('name', $builder->fields[0]['child'], 'MkdirType::buildForm() should define filename field.');
        $this->assertEquals('text', $builder->fields[0]['type'], 'MkdirType::buildForm() should define filename as text field.');
    }

    /**
     * @test
     * @version 0.0.1
     * @since 0.0.1
     */
    public function mkdirNameValidator()
    {
        $type = new MkdirType(\realpath(__DIR__ . '/../../fixtures/fs') . '/');

        $options = new OptionsResolver();
        $type->setDefaultOptions($options);
        $constraints = $options->resolve()['constraints'];
        $constraints->fields = ['name' => $constraints->fields['name']];

        // create value validator
        $validator = new Validator(
            new ClassMetadataFactory(),
            new ConstraintValidatorFactory()
        );

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

class MockFormBuilder extends FormBuilder
{
    public $fields = [];

    public function __construct()
    {
    }

    public function add($child, $type = null, array $options = array())
    {
        $this->fields[] = [
            'child' => $child,
            'type' => $type,
            'options' => $options,
        ];

        return $this;
    }
}
