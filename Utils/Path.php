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

namespace ChillDev\Bundle\FileManagerBundle\Utils;

use UnexpectedValueException;

/**
 * Path-related utilities.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.2
 * @since 0.0.2
 * @package ChillDev\Bundle\FileManagerBundle
 */
class Path
{
    /**
     * Resolves symbolic elements in path.
     *
     * @param string $path User-specified path.
     * @return string Normalized path.
     * @throws UnexpectedValueException When a path is invalid.
     * @version 0.0.2
     * @since 0.0.2
     */
    public static function resolve($path)
    {
        // make sure it's absolute path
        $path = '/' . $path . '/';

        // resolve all symbolic references
        $path = \preg_replace('#//+#', '/', $path);
        while (\preg_match('#/([^/]+/\\.)?\\./#', $path, $match, \PREG_OFFSET_CAPTURE) > 0) {
            $path = \substr_replace($path, '/', $match[0][1], \strlen($match[0][0]));
        }

        // reference outside root path
        if (\strpos($path, '/../') !== false) {
            throw new UnexpectedValueException('Specified path contains invalid reference that exceeds disk scope.');
        }

        // strip added slashes
        return \substr($path, 1, -1);
    }
}
