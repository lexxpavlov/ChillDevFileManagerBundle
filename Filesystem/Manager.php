<?php

/**
 * This file is part of the ChillDev FileManager bundle.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.1
 * @since 0.0.1
 * @package ChillDev\Bundle\FileManagerBundle
 */

namespace ChillDev\Bundle\FileManagerBundle\Filesystem;

use ArrayObject;

use ChillDev\Bundle\FileManagerBundle\Filesystem\Disk;

/**
 * Disks manager.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.1
 * @since 0.0.1
 * @package ChillDev\Bundle\FileManagerBundle
 */
class Manager extends ArrayObject
{
    /**
     * Creates new disk reference.
     *
     * @param string $id Disk identifier.
     * @param string $label Disk label.
     * @param string $source Destination directory.
     * @return self Self instance.
     * @version 0.0.1
     * @since 0.0.1
     */
    public function createDisk($id, $label, $source)
    {
        $this[$id] = new Disk($id, $label, $source);

        return $this;
    }
}
