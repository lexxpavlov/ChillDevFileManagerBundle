<?php

/**
 * This file is part of the ChillDev FileManager bundle.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012 - 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.2
 * @since 0.0.1
 * @package ChillDev\Bundle\FileManagerBundle
 */

namespace ChillDev\Bundle\FileManagerBundle\Tests\Validator\Constraints;

use PHPUnit_Framework_TestCase;

use ChillDev\Bundle\FileManagerBundle\Validator\Constraints\FileNotExists;

/**
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012 - 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.2
 * @since 0.0.1
 * @package ChillDev\Bundle\FileManagerBundle
 */
class FileNotExistsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException Symfony\Component\Validator\Exception\MissingOptionsException
     * @expectedExceptionMessage The options "filesystem" must be set for constraint ChillDev\Bundle\FileManagerBundle\Validator\Constraints\FileNotExists
     * @version 0.0.2
     * @since 0.0.2
     */
    public function constraintRequiredFilesystem()
    {
        new FileNotExists([
            'path' => '',
        ]);
    }

    /**
     * @test
     * @expectedException Symfony\Component\Validator\Exception\MissingOptionsException
     * @expectedExceptionMessage The options "path" must be set for constraint ChillDev\Bundle\FileManagerBundle\Validator\Constraints\FileNotExists
     * @version 0.0.2
     * @since 0.0.2
     */
    public function constraintRequiredPath()
    {
        new FileNotExists([
            'filesystem' => '',
        ]);
    }
}
