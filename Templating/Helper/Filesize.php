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

namespace ChillDev\Bundle\FileManagerBundle\Templating\Helper;

use Symfony\Component\Templating\Helper\Helper;

/**
 * Filesize formatting helper.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.2
 * @since 0.0.2
 * @package ChillDev\Bundle\FileManagerBundle
 */
class Filesize extends Helper
{
    /**
     * Size prefixes.
     *
     * @var string[]
     * @version 0.0.2
     * @since 0.0.2
     */
    protected static $units = ['', 'k', 'M', 'G', 'T', 'P', 'E', 'Z', 'Y'];

    /**
     * Returns helpers alias.
     *
     * @return string Helper name.
     * @version 0.0.2
     * @since 0.0.2
     */
    public function getName()
    {
        return 'filesize';
    }

    /**
     * Formats filesize.
     *
     * @param int $size Filesize.
     * @param int $divisor Unit step divisor.
     * @return string Human-readable size.
     * @version 0.0.2
     * @since 0.0.2
     */
    public function filesize($size, $divisor = 1024)
    {
        $unit = 0;
        $units = \count(self::$units) - 1;

        // move one step higher if needed and possible
        while ($size >= $divisor && $unit < $units) {
            $size /= $divisor;
            ++$unit;
        }

        return (\round($size) == $size ? $size : \number_format($size, 2, '.', ' ')) . ' ' . self::$units[$unit] . 'B';
    }

    /**
     * Shorter way for invoking helper method.
     *
     * @param int $size Filesize.
     * @return string Human-readable size.
     * @version 0.0.2
     * @since 0.0.2
     */
    public function __invoke($size, $divisor = 1024)
    {
        return $this->filesize($size, $divisor);
    }
}
