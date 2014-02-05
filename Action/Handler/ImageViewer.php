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
use ChillDev\Bundle\FileManagerBundle\Utils\Controller;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Simple image viewer.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2014 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.3
 * @since 0.1.3
 * @package ChillDev\Bundle\FileManagerBundle
 */
class ImageViewer implements HandlerInterface
{
    /**
     * Templating engine.
     *
     * @var EngineInterface
     * @version 0.1.3
     * @since 0.1.3
     */
    protected $templating;

    /**
     * Initializes object with all dependencies.
     *
     * @param EngineInterface $templating Templating engine.
     * @version 0.1.3
     * @since 0.1.3
     */
    public function __construct(EngineInterface $templating)
    {
        $this->templating = $templating;
    }

    /**
     * {@inheritDoc}
     * @return string Action label text.
     * @version 0.1.3
     * @since 0.1.3
     */
    public function getLabel()
    {
        return 'View';
    }

    /**
     * {@inheritDoc}
     * @param string $mimeType File MIME type.
     * @return bool Whether filetype is supported or not.
     * @version 0.1.3
     * @since 0.1.3
     */
    public function supports($mimeType)
    {
        return preg_match('#^image/#', $mimeType) > 0;
    }

    /**
     * {@inheritDoc}
     * @param Request $request Request object.
     * @param Disk $disk Disk reference.
     * @param string $path Action subject.
     * @return \Symfony\Component\HttpFoundation\Response Response.
     * @throws HttpException When target path is not a regular file.
     * @version 0.1.3
     * @since 0.1.3
     */
    public function handle(Request $request, Disk $disk, $path)
    {
        // get file handle
        $filesystem = $disk->getFilesystem();

        Controller::ensureDirectoryFlag($disk, $filesystem, $path, false);

        return $this->templating->renderResponse(
            'ChillDevFileManagerBundle:Action:image-viewer.html.default',
            ['disk' => $disk, 'path' => $path]
        );
    }
}
