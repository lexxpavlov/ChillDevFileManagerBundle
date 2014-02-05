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

namespace ChillDev\Bundle\FileManagerBundle\Translation;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Flash messages container that handles localization automatically.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2014 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.3
 * @since 0.1.3
 * @package ChillDev\Bundle\FileManagerBundle
 */
class FlashBag
{
    /**
     * Messages container.
     *
     * @var FlashBagInterface
     * @version 0.1.3
     * @since 0.1.3
     */
    protected $flashBag;

    /**
     * Messages translator.
     *
     * @var TranslatorInterface
     * @version 0.1.3
     * @since 0.1.3
     */
    protected $translator;

    /**
     * Initializes object.
     *
     * @param FlashBagInterface $flashBag Messages container.
     * @param TranslatorInterface $translator Messages translator.
     * @version 0.1.3
     * @since 0.1.3
     */
    public function __construct(FlashBagInterface $flashBag, TranslatorInterface $translator)
    {
        $this->flashBag = $flashBag;
        $this->translator = $translator;
    }

    /**
     * Adds session flash message.
     *
     * @param string $type Message type.
     * @param string $message Message template.
     * @param array $params Message parameters.
     * @version 0.1.3
     * @since 0.1.3
     */
    public function add($type, $message, array $params = [])
    {
        $this->flashBag->add(
            $type,
            $this->translator->trans($message, $params)
        );
    }
}
