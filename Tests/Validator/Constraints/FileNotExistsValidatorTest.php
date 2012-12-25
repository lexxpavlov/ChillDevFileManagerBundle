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

namespace ChillDev\Bundle\FileManagerBundle\Tests\Validator\Constraints;

use PHPUnit_Framework_TestCase;

use ChillDev\Bundle\FileManagerBundle\Validator\Constraints\FileNotExists;

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
class FileNotExistsValidatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @version 0.0.1
     * @since 0.0.1
     */
    public function constraintDefaultOption()
    {
        // create value validator
        $validator = new Validator(
            new ClassMetadataFactory(),
            new ConstraintValidatorFactory()
        );

        $constraint = new FileNotExists(\realpath(__DIR__ . '/../../fixtures/fs') . '/');

        $this->assertCount(1, $validator->validateValue('foo', $constraint), 'FileNotExistsValidator::validate() should report validation error if file already exists.');
        $this->assertCount(0, $validator->validateValue('fooo', $constraint), 'FileNotExistsValidator::validate() should not report validation error if file doesn\'t exists.');
    }
}
