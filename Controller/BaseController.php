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

namespace ChillDev\Bundle\FileManagerBundle\Controller;

use LogicException;

use ChillDev\Bundle\FileManagerBundle\Filesystem\Disk;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Base routines for controllers.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2014 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.3
 * @since 0.1.3
 * @package ChillDev\Bundle\FileManagerBundle
 */
abstract class BaseController extends Controller
{
    /**
     * Logs user action with current user session info.
     *
     * @param Disk $disk Current disk scope.
     * @param string $message Message pattern.
     * @version 0.1.3
     * @since 0.1.3
     */
    protected function logUserAction(Disk $disk, $message)
    {
        // log username if security is enabled
        try {
            $user = '"' . $this->getUser() . '"';
        } catch (LogicException $error) {
            $user = '~anonymous';
        }

        $this->get('logger')->info(
            sprintf($message . ' by user %s.', $user),
            ['scope' => $disk->getSource()]
        );
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
    protected function addFlashMessage($type, $message, array $params = [])
    {
        $this->get('chilldev.filemanager.translation.flash_bag')->add(
            $type,
            $message,
            $params
        );
    }
}
