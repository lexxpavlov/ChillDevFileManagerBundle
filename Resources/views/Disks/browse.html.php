<?php

/**
 * This file is part of the ChillDev FileManager bundle.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012 - 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.3
 * @since 0.0.1
 * @package ChillDev\Bundle\FileManagerBundle
 */

$view->extend('ChillDevFileManagerBundle::layout.html.php');

$view['title']->append($view['translator']->trans('Browsing path %disk%/%path%', ['%disk%' => $disk, '%path%' => $path]));

?>
<h1><a href="<?php echo $view['router']->generate('chilldev_filemanager_disks_list'); ?>"><?php echo $view['translator']->trans('File manager'); ?></a> » <a href="<?php echo $view['router']->generate('chilldev_filemanager_disks_browse', ['disk' => $disk->getId(), 'path' => $path]); ?>"><?php echo $view->escape($disk . '/' . $path); ?></a></h1>

<menu>
    <li><a href="<?php echo $view['router']->generate('chilldev_filemanager_files_mkdir', ['disk' => $disk->getId(), 'path' => $path]); ?>"><?php echo $view['translator']->trans('Create directory'); ?></a></li>
    <li><a href="<?php echo $view['router']->generate('chilldev_filemanager_files_upload', ['disk' => $disk->getId(), 'path' => $path]); ?>"><?php echo $view['translator']->trans('Upload file'); ?></a></li>
</menu>

<table>
    <thead>
        <tr>
            <th>
                <?php echo $view['translator']->trans('Filename'); ?>
                <a href="<?php echo $view['router']->generate('chilldev_filemanager_disks_browse', ['disk' => $disk->getId(), 'path' => $path, 'by' => 'path', 'order' => 1]); ?>">▲</a>
                <a href="<?php echo $view['router']->generate('chilldev_filemanager_disks_browse', ['disk' => $disk->getId(), 'path' => $path, 'by' => 'path', 'order' => -1]); ?>">▼</a>
            </th>
            <th>
                <?php echo $view['translator']->trans('Size'); ?>
                <a href="<?php echo $view['router']->generate('chilldev_filemanager_disks_browse', ['disk' => $disk->getId(), 'path' => $path, 'by' => 'size', 'order' => 1]); ?>">▲</a>
                <a href="<?php echo $view['router']->generate('chilldev_filemanager_disks_browse', ['disk' => $disk->getId(), 'path' => $path, 'by' => 'size', 'order' => -1]); ?>">▼</a>
            </th>
            <th></th>
        </tr>
    </thead>

    <tbody>
        <?php if (!empty($path)): ?>
            <tr>
                <td colspan="3"><a href="<?php echo $view['router']->generate('chilldev_filemanager_disks_browse', ['disk' => $disk->getId(), 'path' => \dirname($path)]); ?>">..</a></td>
            </tr>
        <?php endif; ?>
        <?php if (\count($list) > 0): ?>
            <?php foreach ($list as $file => $info): ?>
                <tr>
                    <td>
                        <?php if ($info['isDirectory']): ?>
                            <a href="<?php echo $view['router']->generate('chilldev_filemanager_disks_browse', ['disk' => $disk->getId(), 'path' => $info['path']]); ?>"><?php echo $view->escape($file); ?></a>
                        <?php else: ?>
                            <a href="<?php echo $view['router']->generate('chilldev_filemanager_files_download', ['disk' => $disk->getId(), 'path' => $info['path']]); ?>"><?php echo $view->escape($file); ?></a>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (isset($info['size'])): ?>
                            <?php echo $view['filesize']->filesize($info['size']); ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form action="<?php echo $view['router']->generate('chilldev_filemanager_files_delete', ['disk' => $disk->getId(), 'path' => $info['path']]); ?>" method="post">
                            <input type="submit" value="<?php echo $view['translator']->trans('Delete'); ?>"/>
                        </form>
                        <a href="<?php echo $view['router']->generate('chilldev_filemanager_files_rename', ['disk' => $disk->getId(), 'path' => $info['path']]); ?>"><?php echo $view['translator']->trans('Rename'); ?></a>
                        <a href="<?php echo $view['router']->generate('chilldev_filemanager_files_move', ['disk' => $disk->getId(), 'path' => $info['path']]); ?>"><?php echo $view['translator']->trans('Move'); ?></a>
                        <a href="<?php echo $view['router']->generate('chilldev_filemanager_files_copy', ['disk' => $disk->getId(), 'path' => $info['path']]); ?>"><?php echo $view['translator']->trans('Copy'); ?></a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="3"><?php echo $view['translator']->trans('This directory is empty.'); ?></td>
            <tr>
        <?php endif; ?>
    </tbody>
</table>
