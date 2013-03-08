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

$view['title']->append($view['translator']->trans($title, ['%disk%' => $disk, '%path%' => $path]));

?>
<h1><a href="<?php echo $view['router']->generate($route, ['disk' => $disk->getId(), 'path' => $path, 'destination' => $destination]); ?>"><?php echo $view->escape($disk . '/' . $destination); ?></a></h1>

<form action="<?php echo $view['router']->generate($route, ['disk' => $disk->getId(), 'path' => $path, 'destination' => $destination]); ?>" method="post">
    <input type="submit" value="<?php echo $view['translator']->trans('Here'); ?>"/>
</form>

<table>
    <thead>
        <tr>
            <th>
                <?php echo $view['translator']->trans('Filename'); ?>
                <a href="<?php echo $view['router']->generate($route, ['disk' => $disk->getId(), 'path' => $path, 'destination' => $destination, 'order' => 1]); ?>">▲</a>
                <a href="<?php echo $view['router']->generate($route, ['disk' => $disk->getId(), 'path' => $path, 'destination' => $destination, 'order' => -1]); ?>">▼</a>
            </th>
        </tr>
    </thead>

    <tbody>
        <?php if (!empty($destination)): ?>
            <tr>
                <td><a href="<?php echo $view['router']->generate($route, ['disk' => $disk->getId(), 'path' => $path, 'destination' => \dirname($destination)]); ?>">..</a></td>
            </tr>
        <?php endif; ?>
        <?php if (\count($list) > 0): ?>
            <?php foreach ($list as $file => $info): ?>
                <tr>
                    <td>
                        <?php if ($info['isDirectory']): ?>
                            <a href="<?php echo $view['router']->generate($route, ['disk' => $disk->getId(), 'path' => $path, 'destination' => $info['path']]); ?>"><?php echo $view->escape($file); ?></a>
                        <?php else: ?>
                            <?php echo $view->escape($file); ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td><?php echo $view['translator']->trans('This directory is empty.'); ?></td>
            <tr>
        <?php endif; ?>
    </tbody>
</table>
