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

namespace ChillDev\Bundle\FileManagerBundle\Form\Type;

use ChillDev\Bundle\FileManagerBundle\Validator\Constraints\FileNotExists;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Collection;

/**
 * Simply filename field form.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.1
 * @since 0.0.1
 * @package ChillDev\Bundle\FileManagerBundle
 */
class MkdirType extends AbstractType
{
    /**
     * Destination location path.
     *
     * @var string
     * @version 0.0.1
     * @since 0.0.1
     */
    protected $realpath;

    /**
     * Initializes form type.
     *
     * @param string $realpath Destination location path.
     * @version 0.0.1
     * @since 0.0.1
     */
    public function __construct($realpath)
    {
        $this->realpath = $realpath;
    }

    /**
     * {@inheritDoc}
     * @version 0.0.1
     * @since 0.0.1
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', ['label' => 'New directory name:']);
    }

    /**
     * {@inheritDoc}
     * @version 0.0.1
     * @since 0.0.1
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $constraints = new Collection(
            [
                'name' => [
                    new NotBlank(),
                    new Regex(
                        [
                            'pattern' => '#^(\\.{1,2}|.*[\\x00/?*:;{}\\\\].*)$#',
                            'match' => false,
                            'message' => 'Invalid filename.'
                        ]
                    ),
                    new FileNotExists($this->realpath),
                ],
            ]
        );

        $resolver->setDefaults(['constraints' => $constraints]);
    }

    /**
     * {@inheritDoc}
     * @version 0.0.1
     * @since 0.0.1
     */
    public function getName()
    {
        return 'mkdir';
    }
}
