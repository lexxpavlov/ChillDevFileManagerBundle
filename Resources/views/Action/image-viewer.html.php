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

$view->extend('ChillDevFileManagerBundle::layout.html.php');

$view['title']->append($view['translator']->trans('Viewing image %disk%/%path%', ['%disk%' => $disk, '%path%' => $path]));

?>
<img src="<?php echo $view['router']->generate('chilldev_filemanager_files_download', ['disk' => $disk->getId(), 'path' => $path]) ?>" alt="<?php $view['translator']->trans('Image %disk%/%path% preview', ['%disk%' => $disk, '%path%' => $path]); ?>"/>
