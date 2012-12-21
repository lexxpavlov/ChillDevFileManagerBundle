<?php

/**
 * Disks listing.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.1
 * @since 0.0.1
 * @package ChillDev\Bundle\FileManagerBundle
 */

$view->extend('ChillDevFileManagerBundle::layout.html.php');

$view['title']->append($view['translator']->trans('Disks list'));

?>
<?php if (\count($disks) > 0): ?>
    <ul>
        <?php foreach ($disks as $id => $disk): ?>
            <li><a href="<?php echo $view['router']->generate('chilldev_filemanager_disks_browse', ['disk' => $id]); ?>"><?php echo $view->escape($disk); ?></a></li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p><?php echo $view['translator']->trans('There are no disks configured for file manager.'); ?></p>
<?php endif; ?>
