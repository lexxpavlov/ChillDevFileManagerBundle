<?php

/**
 * This file is part of the ChillDev FileManager bundle.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.2
 * @since 0.1.2
 * @package ChillDev\Bundle\FileManagerBundle
 */

namespace ChillDev\Bundle\FileManagerBundle\Block;

use ChillDev\Bundle\FileManagerBundle\Filesystem\Manager;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\BlockBundle\Block\BaseBlockService;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Model\BlockInterface;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Disks listing block for Sonata Admin integration.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.2
 * @since 0.1.2
 * @package ChillDev\Bundle\FileManagerBundle
 */
class DisksList extends BaseBlockService
{
    /**
     * Disks manager.
     *
     * @var Manager
     * @version 0.1.2
     * @since 0.1.2
     */
    protected $manager;

    /**
     * Initializes block object.
     *
     * @param string $name Block ID.
     * @param EngineInterface $templating Templating engine.
     * @param Manager $manager Disks manager.
     * @version 0.1.2
     * @since 0.1.2
     */
    public function __construct($name, EngineInterface $templating, Manager $manager)
    {
        parent::__construct($name, $templating);

        $this->manager = $manager;
    }
    /**
     * {@inheritDoc}
     * @version 0.1.2
     * @since 0.1.2
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        return $this->renderResponse(
            $blockContext->getTemplate(),
            [
                'block' => $blockContext->getBlock(),
                'settings' => $blockContext->getSettings(),
                'disks' => $this->manager,
            ],
            $response
        );
    }

    /**
     * {@inheritDoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @version 0.1.2
     * @since 0.1.2
     */
    public function validateBlock(ErrorElement $errorElement, BlockInterface $block)
    {
    }

    /**
     * {@inheritDoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @version 0.1.2
     * @since 0.1.2
     */
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
    }

    /**
     * {@inheritDoc}
     * @version 0.1.2
     * @since 0.1.2
     */
    public function setDefaultSettings(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'template' => 'ChillDevFileManagerBundle:Block:disks_list.html.default'
            ]
        );
    }

    /**
     * {@inheritDoc}
     * @version 0.1.2
     * @since 0.1.2
     */
    public function getName()
    {
        return 'Disks list';
    }
}
