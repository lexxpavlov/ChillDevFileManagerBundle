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

namespace ChillDev\Bundle\FileManagerBundle\Tests\Validator\Constraints;

use PHPUnit_Framework_TestCase;

use ChillDev\Bundle\FileManagerBundle\Validator\Constraints\Filename;

use Symfony\Component\Validator\Constraints\Regex;

/**
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.3
 * @since 0.0.3
 * @package ChillDev\Bundle\FileManagerBundle
 */
class FilenameTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @version 0.0.3
     * @since 0.0.3
     */
    public function validatedByRegex()
    {
        $this->assertEquals((new Regex(''))->validatedBy(), (new Filename())->validatedBy(), 'Filename constraint should be validated by Regex constraint validator.');
    }

    /**
     * @test
     * @version 0.0.3
     * @since 0.0.3
     */
    public function constraintDefaultOption()
    {
        $flag = new \stdClass();

        $constraint = new Filename($flag);

        $this->assertSame($flag, $constraint->match, 'Filename should use "match" as default option.');
    }

    /**
     * @test
     * @version 0.0.3
     * @since 0.0.3
     */
    public function constraintNoRequiredOptions()
    {
        new Filename();
        $this->assertTrue(true, 'Filename should use require any option.');
    }
}
