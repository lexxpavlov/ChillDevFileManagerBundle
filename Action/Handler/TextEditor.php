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

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

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
     * Session.
     *
     * @var Session
     * @version 0.1.3
     * @since 0.1.3
     */
    protected $session;

    /**
     * Translator.
     *
     * @var TranslatorInterface
     * @version 0.1.3
     * @since 0.1.3
     */
    protected $translator;

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
     * @param Session $session Current session.
     * @param TranslatorInterface $translator Messages translator.
     * @param FormFactory $formFactory Form factory.
     * @version 0.1.3
     * @since 0.1.3
     */
    public function __construct(
        EngineInterface $templating,
        UrlGeneratorInterface $router,
        Session $session,
        TranslatorInterface $translator,
        FormFactory $formFactory
    ) {
        $this->templating = $templating;
        $this->router = $router;
        $this->session = $session;
        $this->translator = $translator;
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
        $info = $filesystem->getFileInfo($path);

        if (!$info->isFile()) {
            throw new HttpException(
                400,
                sprintf('"%s/%s" is not a regular file that can be edited.', $disk, $path)
            );
        }

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

            $this->session->getFlashBag()->add(
                'done',
                $this->translator->trans('File "%file%" saved.', ['%file%' => $disk . '/' . $path])
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
