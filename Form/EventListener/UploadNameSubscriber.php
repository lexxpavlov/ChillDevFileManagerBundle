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

namespace ChillDev\Bundle\FileManagerBundle\Form\EventListener;

use ChillDev\Bundle\FileManagerBundle\Validator\Constraints\FileNotExists;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints\Collection;

/**
 * Handles file default name setting.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.3
 * @since 0.0.3
 * @package ChillDev\Bundle\FileManagerBundle
 */
class UploadNameSubscriber implements EventSubscriberInterface
{
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
     * Initializes form listener.
     *
     * @param Collection $constraints Validation constraints.
     * @param FileNotExists $check File existance check.
     * @version 0.0.3
     * @since 0.0.3
     */
    public function __construct(Collection $constraints, FileNotExists $check)
    {
        $this->constraints = $constraints;
        $this->check = $check;
    }

    /**
     * {@inheritDoc}
     * @version 0.0.3
     * @since 0.0.3
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_BIND => 'preBind',
        ];
    }

    /**
     * Handles default file name setting.
     *
     * @param FormEvent $event Form event.
     * @version 0.0.3
     * @since 0.0.3
     */
    public function preBind(FormEvent $event)
    {
        $data = $event->getData();

        // set default filename for uploaded file as original filename
        if ((!isset($data['name']) || empty($data['name']))
            && isset($data['file'])
            && $data['file'] instanceof UploadedFile
        ) {
            $data['name'] = $data['file']->getClientOriginalName();
            $event->setData($data);
        }

        // only check for file existance if no force flag is set
        if (!(isset($data['force']) && $data['force'])) {
            $this->constraints->fields['name']->constraints[] = $this->check;
        }
    }
}
