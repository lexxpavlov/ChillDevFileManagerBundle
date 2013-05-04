<?php

/**
 * This file is part of the ChillDev FileManager bundle.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012 - 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.1
 * @since 0.0.1
 * @package ChillDev\Bundle\FileManagerBundle
 */

namespace ChillDev\Bundle\FileManagerBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Constraint validator.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012 - 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.1
 * @since 0.0.1
 * @package ChillDev\Bundle\FileManagerBundle
 */
class FileNotExistsValidator extends ConstraintValidator
{
    /**
     * {@inheritDoc}
     * @version 0.1.1
     * @since 0.0.1
     */
    public function validate($value, Constraint $constraint)
    {
        $file = $constraint->path . '/' . $value;
        if (\strpos($file, \chr(0)) === false && $constraint->filesystem->exists($file)) {
            $this->context->addViolation($constraint->message, ['%file%' => $file]);
        }
    }
}
