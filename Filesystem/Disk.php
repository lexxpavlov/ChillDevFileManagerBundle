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

namespace ChillDev\Bundle\FileManagerBundle\Filesystem;

/**
 * Disk reference.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2012 - 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.0.3
 * @since 0.0.1
 * @package ChillDev\Bundle\FileManagerBundle
 */
class Disk
{
    /**
     * Disk ID.
     *
     * @var string
     * @version 0.0.1
     * @since 0.0.1
     */
    protected $id;

    /**
     * Disk label.
     *
     * @var string
     * @version 0.0.1
     * @since 0.0.1
     */
    protected $label;

    /**
     * Wrapped directory.
     *
     * @var string
     * @version 0.0.1
     * @since 0.0.1
     */
    protected $source;

    /**
     * Initializes disk definition.
     *
     * @param string $id Disk ID.
     * @param string $label Disk label.
     * @param string $source Enclosing directory.
     * @version 0.0.1
     * @since 0.0.1
     */
    public function __construct($id, $label, $source)
    {
        $this->id = $id;
        $this->label = $label;
        $this->source = $source;
    }

    /**
     * Returns disk ID.
     *
     * @return string ID.
     * @version 0.0.1
     * @since 0.0.1
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns disk label.
     *
     * @return string Label.
     * @version 0.0.1
     * @since 0.0.1
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Returns disk source.
     *
     * @return string Directory path.
     * @version 0.0.1
     * @since 0.0.1
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Returns string representation.
     *
     * @return string Printable string.
     * @version 0.0.1
     * @since 0.0.1
     */
    public function __toString()
    {
        return '[' . $this->getLabel() . ']';
    }

    /**
     * Returns filesystem for given disk.
     *
     * @return Filesystem Filesystem configured for current disk.
     * @version 0.0.3
     * @since 0.0.2
     */
    public function getFilesystem()
    {
        return new Filesystem($this->source);
    }
}
