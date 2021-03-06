<?php

/**
 * This file is part of the ChillDev FileManager bundle.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.2
 * @since 0.0.2
 * @package ChillDev\Bundle\FileManagerBundle
 */

namespace ChillDev\Bundle\FileManagerBundle\Tests\Templating\Helper;

use PHPUnit_Framework_TestCase;

use ChillDev\Bundle\FileManagerBundle\Templating\Helper\FilesizeFormatter;

/**
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.2
 * @since 0.0.2
 * @package ChillDev\Bundle\FileManagerBundle
 */
class FilesizeFormatterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @version 0.1.2
     * @since 0.0.2
     */
    public function getHelperName()
    {
        $this->assertEquals('filesize', (new FilesizeFormatter())->getName(), 'FilesizeFormatter::getName() should return helper alias.');
    }

    /**
     * Check default filesize formatting.
     *
     * @test
     * @version 0.1.2
     * @since 0.0.2
     */
    public function defaultFilesizeDivisor()
    {
        $filesize = new FilesizeFormatter();

        $this->assertEquals('100 B', $filesize->filesize(100), 'FilesizeFormatter::filesize() should format size in bytes to be displayed with unit.');
        $this->assertEquals('100 kB', $filesize->filesize(102400), 'FilesizeFormatter::filesize() should format size in bytes to be displayed with unit with pefix.');
        $this->assertEquals('100.50 MB', $filesize->filesize(105381888), 'FilesizeFormatter::filesize() should format size in bytes to upper unit even if it\'s not rounded and format it with two digits after period.');
    }

    /**
     * Check custom filesize divisor.
     *
     * @test
     * @version 0.1.2
     * @since 0.0.2
     */
    public function customFilesizeDivisor()
    {
        $filesize = new FilesizeFormatter();

        $this->assertEquals('100 B', $filesize->filesize(100, 1000), 'FilesizeFormatter::filesize() should format size in bytes to be displayed with unit, using specified custom divisor.');
        $this->assertEquals('100 kB', $filesize->filesize(100000, 1000), 'FilesizeFormatter::filesize() should format size in bytes to be displayed with unit with pefix, using specified custom divisor.');
        $this->assertEquals('100.50 MB', $filesize->filesize(100500000, 1000), 'FilesizeFormatter::filesize() should format size in bytes to upper unit even if it\'s not rounded and format it with two digits after period, using specified custom divisor.');
    }

    /**
     * Check direct invoking call.
     *
     * @test
     * @version 0.1.2
     * @since 0.0.2
     */
    public function directInvoke()
    {
        $filesize = new FilesizeFormatter();
        $this->assertEquals('100 B', $filesize->__invoke(100), 'FilesizeFormatter::__invoke() should format filesize.');
    }

    /**
     * Check handling of inline invoking.
     *
     * @test
     * @version 0.1.2
     * @since 0.0.2
     */
    public function callableInvoke()
    {
        $filesize = new FilesizeFormatter();
        $this->assertEquals('100 B', $filesize(100), 'FilesizeFormatter::__invoke() should handle inline invocation of object as function.');
    }
}
