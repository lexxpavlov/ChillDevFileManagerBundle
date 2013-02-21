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

namespace ChillDev\Bundle\FileManagerBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraints\Regex;

/**
 * Filename matching regex.
 *
 * @Annotation
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.3
 * @since 0.0.3
 * @package ChillDev\Bundle\FileManagerBundle
 */
class Filename extends Regex
{
    /**
     * Default error message.
     *
     * @var string
     * @version 0.0.3
     * @since 0.0.3
     */
    public $message = 'Invalid filename.';

    /**
     * Regex pattern.
     *
     * @var string
     * @version 0.0.3
     * @since 0.0.3
     */
    public $pattern = '#^(\\.{1,2}|.*[\\x00/?*:;{}\\\\].*)$#';

    /**
     * Desired matching result.
     *
     * @var bool
     * @version 0.0.3
     * @since 0.0.3
     */
    public $match = false;

    /**
     * {@inheritDoc}
     * @version 0.0.3
     * @since 0.0.3
     */
    public function validatedBy()
    {
        return 'Symfony\\Component\\Validator\\Constraints\\RegexValidator';
    }

    /**
     * {@inheritDoc}
     * @version 0.0.3
     * @since 0.0.3
     */
    public function getDefaultOption()
    {
        return 'match';
    }

    /**
     * {@inheritDoc}
     * @version 0.0.3
     * @since 0.0.3
     */
    public function getRequiredOptions()
    {
        return [];
    }
}
