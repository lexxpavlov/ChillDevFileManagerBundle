<?php

/**
 * This file is part of the ChillDev FileManager bundle.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.2
 * @since 0.0.3
 * @package ChillDev\Bundle\FileManagerBundle
 */

namespace ChillDev\Bundle\FileManagerBundle\Form\Type;

use ChillDev\Bundle\FileManagerBundle\Filesystem\Filesystem;
use ChillDev\Bundle\FileManagerBundle\Validator\Constraints\FileNotExists;
use ChillDev\Bundle\FileManagerBundle\Validator\Constraints\Filename;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Simply filename field form.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.3
 * @since 0.0.3
 * @package ChillDev\Bundle\FileManagerBundle
 */
class RenameType extends AbstractType
{
    /**
     * Filesystem.
     *
     * @var Filesystem
     * @version 0.0.3
     * @since 0.0.3
     */
    protected $filesystem;

    /**
     * Destination location path.
     *
     * @var string
     * @version 0.0.3
     * @since 0.0.3
     */
    protected $path;

    /**
     * Initializes form type.
     *
     * @param Filesystem $filesystem Filesystem manager.
     * @param string $path Destination location path.
     * @version 0.0.3
     * @since 0.0.3
     */
    public function __construct(Filesystem $filesystem, $path)
    {
        $this->filesystem = $filesystem;
        $this->path = $path;
    }

    /**
     * {@inheritDoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @version 0.0.3
     * @since 0.0.3
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', ['label' => 'New name:']);
    }

    /**
     * {@inheritDoc}
     * @version 0.0.3
     * @since 0.0.3
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $constraints = new Collection(
            [
                'name' => [
                    new NotBlank(),
                    new Filename(),
                    new FileNotExists(
                        [
                            'filesystem' => $this->filesystem,
                            'path' => $this->path,
                        ]
                    ),
                ],
            ]
        );

        $resolver->setDefaults(['constraints' => $constraints]);
    }

    /**
     * {@inheritDoc}
     * @version 0.0.3
     * @since 0.0.3
     */
    public function getName()
    {
        return 'rename';
    }
}
