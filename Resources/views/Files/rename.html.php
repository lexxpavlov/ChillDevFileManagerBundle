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

$view->extend('ChillDevFileManagerBundle::layout.html.php');

$view['title']->append($view['translator']->trans('Renaming file %disk%/%path%', ['%disk%' => $disk, '%path%' => $path]));

?>
<form action="<?php echo $view['router']->generate('chilldev_filemanager_files_rename', ['disk' => $disk->getId(), 'path' => $path]) ?>" method="post" <?php echo $view['form']->enctype($form) ?>>
    <?php echo $view['form']->widget($form) ?>

    <input type="submit" value="<?php echo $view['translator']->trans('Rename'); ?>"/>
</form>
