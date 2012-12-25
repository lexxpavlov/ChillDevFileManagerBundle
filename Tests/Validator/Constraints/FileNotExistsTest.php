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

/**
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.1
 * @since 0.0.1
 * @package ChillDev\Bundle\FileManagerBundle
 */
class FileNotExistsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @version 0.0.1
     * @since 0.0.1
     */
    public function constraintDefaultOption()
    {
        $path = 'foo';

        $constraint = new FileNotExists($path);

        $this->assertEquals($path, $constraint->path, 'FileNotExists should use "path" as default option.');
    }
}
