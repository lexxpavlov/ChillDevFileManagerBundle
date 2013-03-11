<?php

/**
 * This file is part of the ChillDev FileManager bundle.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012 - 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.0
 * @since 0.0.1
 * @package ChillDev\Bundle\FileManagerBundle
 */

namespace ChillDev\Bundle\FileManagerBundle\Tests\Validator\Constraints;

use ChillDev\Bundle\FileManagerBundle\Tests\BaseContainerTest;
use ChillDev\Bundle\FileManagerBundle\Validator\Constraints\FileNotExists;

use Symfony\Component\Validator\ConstraintValidatorFactory;
use Symfony\Component\Validator\Validator;
use Symfony\Component\Validator\Mapping\ClassMetadataFactory;

/**
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012 - 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.0
 * @since 0.0.1
 * @package ChillDev\Bundle\FileManagerBundle
 */
class FileNotExistsValidatorTest extends BaseContainerTest
{
    /**
     * @test
     * @version 0.0.2
     * @since 0.0.2
     */
    public function fileExistsCheck()
    {
        $filesystem = $this->getMock('ChillDev\\Bundle\\FileManagerBundle\\Filesystem\\Filesystem', [], [], '', false);
        $filesystem->expects($this->at(0))
            ->method('exists')
            ->will($this->returnValue(true));
        $filesystem->expects($this->at(1))
            ->method('exists')
            ->will($this->returnValue(false));

        $constraint = new FileNotExists(['filesystem' => $filesystem, 'path' => '']);

        // create value validator
        $validator = $this->container->get('validator');

        $this->assertCount(1, $validator->validateValue('foo', $constraint), 'FileNotExistsValidator::validate() should report validation error if file already exists.');
        $this->assertCount(0, $validator->validateValue('foo', $constraint), 'FileNotExistsValidator::validate() should not report validation error if file doesn\'t exists.');
    }

    /**
     * @test
     * @version 0.0.2
     * @since 0.0.2
     */
    public function nullCharacter()
    {
        $constraint = new FileNotExists(['filesystem' => $this->getMock('ChillDev\\Bundle\\FileManagerBundle\\Filesystem\\Filesystem', [], [], '', false), 'path' => '']);

        // create value validator
        $validator = $this->container->get('validator');

        $this->assertCount(0, $validator->validateValue(\chr(0), $constraint), 'FileNotExistsValidator::validate() should not report validation error if specified path contains null character.');
    }
}
