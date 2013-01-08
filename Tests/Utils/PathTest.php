<?php

/**
 * This file is part of the ChillDev FileManager bundle.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.2
 * @since 0.0.2
 * @package ChillDev\Bundle\FileManagerBundle
 */

namespace ChillDev\Bundle\FileManagerBundle\Tests\Utils;

use PHPUnit_Framework_TestCase;

use ChillDev\Bundle\FileManagerBundle\Utils\Path;

/**
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.2
 * @since 0.0.2
 * @package ChillDev\Bundle\FileManagerBundle
 */
class PathTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider getValidPaths
     * @version 0.0.2
     * @since 0.0.2
     */
    public function validPathResolving($input, $output)
    {
        $this->assertEquals($output, Path::resolve($input), '"' . $input . '" should be resolved to "' . $output . '".');
    }

    /**
     * @return array
     * @version 0.0.2
     * @since 0.0.2
     */
    public function getValidPaths()
    {
        return [
            ['', ''],
            ['./', ''],
            ['.////foo///bar', 'foo/bar'],
            ['foo/bar', 'foo/bar'],
            ['foo/./bar', 'foo/bar'],
            ['foo/../bar', 'bar'],
            ['foo/bar/..', 'foo'],
        ];
    }

    /**
     * @test
     * @dataProvider getInvalidPaths
     * @expectedException UnexpectedValueException
     * @expectedExceptionMessage Specified path contains invalid reference that exceeds disk scope.
     * @version 0.0.2
     * @since 0.0.2
     */
    public function invalidPathResolving($path)
    {
        Path::resolve($path);
    }

    /**
     * @return array
     * @version 0.0.2
     * @since 0.0.2
     */
    public function getInvalidPaths()
    {
        return [
            ['..'],
            ['foo/bar/../baz/../../..//../../quux'],
        ];
    }
}
