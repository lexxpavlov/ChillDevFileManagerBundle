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

namespace ChillDev\Bundle\FileManagerBundle\Form\Type;

use ChillDev\Bundle\FileManagerBundle\Filesystem\Filesystem;
use ChillDev\Bundle\FileManagerBundle\Form\EventListener\UploadNameSubscriber;
use ChillDev\Bundle\FileManagerBundle\Validator\Constraints\FileNotExists;
use ChillDev\Bundle\FileManagerBundle\Validator\Constraints\Filename;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Collection;

/**
 * File upload form.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.3
 * @since 0.0.3
 * @package ChillDev\Bundle\FileManagerBundle
 */
class UploadType extends AbstractType
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
     * Constraints definitions.
     *
     * @var Collection
     * @version 0.0.3
     * @since 0.0.3
     */
    protected $constraints;

    /**
     * File existance check.
     *
     * @var FileNotExists
     * @version 0.0.3
     * @since 0.0.3
     */
    protected $check;

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
        $this->constraints = new Collection(
            [
                'fields' => [
                    'name' => new Filename(),
                ],
                'allowExtraFields' => true,
            ]
        );
        $this->check = new FileNotExists(
            [
                'filesystem' => $this->filesystem,
                'path' => $this->path,
            ]
        );
    }

    /**
     * {@inheritDoc}
     * @version 0.0.3
     * @since 0.0.3
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new UploadNameSubscriber($this->constraints, $this->check));
        $builder->add('file', 'file', ['label' => 'File:']);
        $builder->add(
            'name',
            'text',
            [
                'label' => 'Uploaded filename:',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Leave blank to keep original filename.',
                ],
            ]
        );
        $builder->add('force', 'checkbox', ['label' => 'Overwrite file if already exists.', 'required' => false, ]);
    }

    /**
     * {@inheritDoc}
     * @version 0.0.3
     * @since 0.0.3
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['constraints' => $this->constraints]);
    }

    /**
     * {@inheritDoc}
     * @version 0.0.3
     * @since 0.0.3
     */
    public function getName()
    {
        return 'upload';
    }
}
