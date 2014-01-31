<?php

/**
 * This file is part of the ChillDev FileManager bundle.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2014 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.3
 * @since 0.1.3
 * @package ChillDev\Bundle\FileManagerBundle
 */

namespace ChillDev\Bundle\FileManagerBundle\Action;

use ArrayObject;

use ChillDev\Bundle\FileManagerBundle\Action\Handler\HandlerInterface;

/**
 * Actions manager.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2014 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.3
 * @since 0.1.3
 * @package ChillDev\Bundle\FileManagerBundle
 */
class ActionsManager extends ArrayObject
{
    /**
     * Actions groupped for types.
     *
     * @var array
     * @version 0.1.3
     * @since 0.1.3
     */
    protected $types = [];

    /**
     * Registers action handler.
     *
     * @param string $action Action name.
     * @param HandlerInterface $handler Handler instance.
     * @return self Self instance.
     * @version 0.1.3
     * @since 0.1.3
     */
    public function registerHandler($action, HandlerInterface $handler)
    {
        $this[$action] = $handler;

        return $this;
    }

    /**
     * Returns list of actions that support given MIME type.
     *
     * @param string $mimeType Target MIME type.
     * @return HandlerInterface[] List of supported actions.
     * @version 0.1.3
     * @since 0.1.3
     */
    public function getActionsForType($mimeType)
    {
        // initialize registry on first call
        if (!isset($this->types[$mimeType])) {
            $this->types[$mimeType] = [];

            // include all supported actions
            foreach ($this as $action => $handler) {
                if ($handler->supports($mimeType)) {
                    $this->types[$mimeType][$action] = $handler;
                }
            }
        }

        return $this->types[$mimeType];
    }
}
