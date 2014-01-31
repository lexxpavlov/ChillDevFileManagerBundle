<?php

/**
 * This file is part of the ChillDev FileManager bundle.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012, 2014 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.3
 * @since 0.0.1
 * @package ChillDev\Bundle\FileManagerBundle
 */

namespace ChillDev\Bundle\FileManagerBundle;

use ChillDev\Bundle\FileManagerBundle\DependencyInjection\ChillDevFileManagerExtension;
use ChillDev\DependencyInjection\Compiler\TagGrabbingPass;
use ChillDev\DependencyInjection\Validator\InterfaceValidator;

use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * File manager bundle.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012, 2014 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.3
 * @since 0.0.1
 * @package ChillDev\Bundle\FileManagerBundle
 */
class ChillDevFileManagerBundle extends Bundle
{
    /**
     * Interface name for action handlers service.
     *
     * @var string
     * @version 0.1.3
     * @since 0.1.3
     */
    const ACTION_INTERFACE_NAME = 'ChillDev\\Bundle\\FileManagerBundle\\Action\\Handler\\HandlerInterface';

    /**
     * {@inheritDoc}
     * @version 0.0.1
     * @since 0.0.1
     */
    public function getContainerExtension()
    {
        // this allows us to have custom extension alias
        // default convention would put a lot of underscores
        if (null === $this->extension) {
            $this->extension = new ChillDevFileManagerExtension();
        }

        return $this->extension;
    }

    /**
     * {@inheritDoc}
     * @version 0.1.3
     * @since 0.1.3
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $pass = new TagGrabbingPass(
            'chilldev.filemanager.action_handler',
            'chilldev.filemanager.actions.actions_manager',
            'registerHandler',
            'action'
        );
        $pass->addValidator(new InterfaceValidator(static::ACTION_INTERFACE_NAME));
        $container->addCompilerPass($pass, PassConfig::TYPE_OPTIMIZE);
    }
}
