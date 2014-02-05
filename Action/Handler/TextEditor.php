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
use ChillDev\Bundle\FileManagerBundle\Form\Type\EditorType;
use ChillDev\Bundle\FileManagerBundle\Translation\FlashBag;
use ChillDev\Bundle\FileManagerBundle\Utils\Controller;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Simple text editor.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2014 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.3
 * @since 0.1.3
 * @package ChillDev\Bundle\FileManagerBundle
 */
class TextEditor implements HandlerInterface
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
     * URLs generator.
     *
     * @var UrlGeneratorInterface
     * @version 0.1.3
     * @since 0.1.3
     */
    protected $router;

    /**
     * Flash messages container.
     *
     * @var FlashBag
     * @version 0.1.3
     * @since 0.1.3
     */
    protected $flashBag;

    /**
     * Form factory.
     *
     * @var FormFactory
     * @version 0.1.3
     * @since 0.1.3
     */
    protected $formFactory;

    /**
     * Initializes object with all dependencies.
     *
     * @param EngineInterface $templating Templating engine.
     * @param UrlGeneratorInterface $router URLs generator.
     * @param FlashBag $flashBag Flash messages container.
     * @param FormFactory $formFactory Form factory.
     * @version 0.1.3
     * @since 0.1.3
     */
    public function __construct(
        EngineInterface $templating,
        UrlGeneratorInterface $router,
        FlashBag $flashBag,
        FormFactory $formFactory
    ) {
        $this->templating = $templating;
        $this->router = $router;
        $this->flashBag = $flashBag;
        $this->formFactory = $formFactory;
    }

    /**
     * {@inheritDoc}
     * @return string Action label text.
     * @version 0.1.3
     * @since 0.1.3
     */
    public function getLabel()
    {
        return 'Edit';
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
        return preg_match('#^text/#', $mimeType) > 0;
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

        $info = $filesystem->getFileInfo($path);

        // initialize form with file content
        $file = $info->openFile();
        $form = $this->formFactory->create(
            new EditorType(),
            [
                'content' => implode(iterator_to_array($file)),
            ]
        );

        // POST requests for saving submited content
        if ($request->isMethod('POST')) {
            $form->bind($request);

            $data = $form->getData();

            // open file for writing
            $file = $info->openFile('w');

            // write content
            $file->fwrite($data['content']);

            $this->flashBag->add(
                'done',
                'File "%file%" saved.',
                ['%file%' => $disk . '/' . $path]
            );

            // go back to directory
            return new RedirectResponse(
                $this->router->generate(
                    'chilldev_filemanager_disks_browse',
                    ['disk' => $disk->getId(), 'path' => dirname($path)]
                )
            );
        }

        return $this->templating->renderResponse(
            'ChillDevFileManagerBundle:Action:text-editor.html.default',
            ['disk' => $disk, 'path' => $path, 'form' => $form->createView()]
        );
    }
}
