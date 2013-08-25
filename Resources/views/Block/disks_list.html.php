<?php

/**
 * This file is part of the ChillDev FileManager bundle.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.2
 * @since 0.1.2
 * @package ChillDev\Bundle\FileManagerBundle
 */

?>
<table class="table table-bordered table-striped sonata-ba-list">
    <thead>
        <tr>
            <th colspan="2"><?php echo $view['translator']->trans('Disks list'); ?></th>
        </tr>
    </thead>

    <tbody>
        <?php if (\count($disks) > 0): ?>
            <?php foreach ($disks as $id => $disk): ?>
                <tr>
                    <td><?php echo $view->escape($disk); ?></td>
                    <td>
                        <div class="btn-group">
                            <a class="btn btn-small" href="<?php echo $view['router']->generate('chilldev_filemanager_disks_browse', ['disk' => $id]); ?>">
                                <span class="icon-folder-open"></span>
                                <?php echo $view['translator']->trans('Browse'); ?>
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="2"><?php echo $view['translator']->trans('There are no disks configured for file manager.'); ?></td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
