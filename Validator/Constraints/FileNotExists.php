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

namespace ChillDev\Bundle\FileManagerBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Asserts that given file does not exist.
 *
 * @Annotation
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012 - 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.2
 * @since 0.0.1
 * @package ChillDev\Bundle\FileManagerBundle
 */
class FileNotExists extends Constraint
{
    /**
     * Default error message.
     *
     * @var string
     * @version 0.0.1
     * @since 0.0.1
     */
    public $message = 'File "%file%" already exists.';

    /**
     * Filesystem.
     *
     * @var ChillDev\Bundle\FileManagerBundle\Filesystem\Filesystem
     * @version 0.0.2
     * @since 0.0.2
     */
    public $filesystem;

    /**
     * Directory scope.
     *
     * @var string
     * @version 0.0.1
     * @since 0.0.1
     */
    public $path;

    /**
     * {@inheritDoc}
     * @version 0.0.2
     * @since 0.0.2
     */
    public function getRequiredOptions()
    {
        return ['filesystem', 'path'];
    }
}
