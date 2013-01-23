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

namespace ChillDev\Bundle\FileManagerBundle\Tests;

use PHPUnit_Framework_TestCase;

/**
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.2
 * @since 0.0.2
 * @package ChillDev\Bundle\FileManagerBundle
 */
abstract class BaseAdapterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @return ChillDev\Bundle\FileManagerBundle\Filesystem\AdapterInterface
     * @version 0.0.2
     * @since 0.0.2
     */
    protected function getAdapterMock()
    {
        return $this->getMock('ChillDev\\Bundle\\FileManagerBundle\\Filesystem\\AdapterInterface');
    }
}
