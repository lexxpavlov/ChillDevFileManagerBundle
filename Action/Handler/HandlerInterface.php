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

namespace ChillDev\Bundle\FileManagerBundle\Action\Handler;

use ChillDev\Bundle\FileManagerBundle\Filesystem\Disk;

use Symfony\Component\HttpFoundation\Request;

/**
 * Action handler interface.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2014 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.3
 * @since 0.1.3
 * @package ChillDev\Bundle\FileManagerBundle
 */
interface HandlerInterface
{
    /**
     * Action label.
     *
     * @return string Action label text.
     * @version 0.1.3
     * @since 0.1.3
     */
    public function getLabel();

    /**
     * Tests if handler supports action on given file.
     *
     * @param string $mimeType File MIME type.
     * @return bool Whether filetype is supported or not.
     * @version 0.1.3
     * @since 0.1.3
     */
    public function supports($mimeType);

    /**
     * Tests if handler supports action on given file.
     *
     * @param Request $request Request object.
     * @param Disk $disk Disk reference.
     * @param string $path Action subject.
     * @return \Symfony\Component\HttpFoundation\Response Response.
     * @version 0.1.3
     * @since 0.1.3
     */
    public function handle(Request $request, Disk $disk, $path);
}
