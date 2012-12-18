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

namespace ChillDev\Bundle\FileManagerBundle\Templating;

use LogicException;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\StreamingEngineInterface;
use Symfony\Component\Templating\TemplateNameParserInterface;

/**
 * Delegated templating engine based on system configuration.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.1
 * @since 0.0.1
 * @package ChillDev\Bundle\FileManagerBundle
 */
class ConfigEngine implements EngineInterface, StreamingEngineInterface
{
    /**
     * DI container.
     *
     * @var ContainerInterface
     * @version 0.0.1
     * @since 0.0.1
     */
    protected $container;

    /**
     * Template name parser.
     *
     * @var TemplateNameParserInterface
     * @version 0.0.1
     * @since 0.0.1
     */
    protected $parser;

    /**
     * Target templating engine.
     *
     * @var string
     * @version 0.0.1
     * @since 0.0.1
     */
    protected $engine;

    /**
     * Initializes templating engine.
     *
     * @param ContainerInterface $container Services container.
     * @param TemplateNameParserInterface $parser Template name parser.
     * @param string $engine Destination templating engine.
     * @version 0.0.1
     * @since 0.0.1
     */
    public function __construct(ContainerInterface $container, TemplateNameParserInterface $parser, $engine)
    {
        $this->container = $container;
        $this->parser = $parser;
        $this->engine = $engine;
    }

    /**
     * Returns templating engine.
     *
     * @return EngineInterface Internal templating engine.
     * @version 0.0.1
     * @since 0.0.1
     */
    protected function getTemplating()
    {
        return $this->container->get('templating');
    }

    /**
     * {@inheritDoc}
     * @version 0.0.1
     * @since 0.0.1
     */
    public function render($name, array $parameters = [])
    {
        $template = $this->parser->parse($name);
        $template->set('engine', $this->engine);
        return $this->getTemplating()->render($template, $parameters);
    }

    /**
     * {@inheritDoc}
     * @version 0.0.1
     * @since 0.0.1
     */
    public function stream($name, array $parameters = [])
    {
        $templating = $this->getTemplating();

        if (!$templating instanceof StreamingEngineInterface) {
            throw new LogicException(
                \sprintf(
                    'Template "%s" cannot be streamed as the sub-sequent target engine "%s"'
                    . ' configured to handle it does not implement StreamingEngineInterface.',
                    $name,
                    \get_class($templating)
                )
            );
        }

        $template = $this->parser->parse($name);
        $template->set('engine', $this->engine);
        $templating->stream($template, $parameters);
    }

    /**
     * {@inheritDoc}
     * @version 0.0.1
     * @since 0.0.1
     */
    public function exists($name)
    {
        $template = $this->parser->parse($name);
        $template->set('engine', $this->engine);
        return $this->getTemplating()->exists($template);
    }

    /**
     * {@inheritDoc}
     * @version 0.0.1
     * @since 0.0.1
     */
    public function supports($name)
    {
        $template = $this->parser->parse($name);

        return $template->get('engine') === 'config';
    }

    /**
     * {@inheritDoc}
     * @version 0.0.1
     * @since 0.0.1
     */
    public function renderResponse($view, array $parameters = [], Response $response = null)
    {
        $template = $this->parser->parse($view);
        $template->set('engine', $this->engine);
        return $this->getTemplating()->renderResponse($template, $parameters, $response);
    }
}
