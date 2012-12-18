<!---
# This file is part of the ChillDev FileManager bundle.
#
# @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
# @copyright 2012 © by Rafał Wrzeszcz - Wrzasq.pl.
# @version 0.0.1
# @since 0.0.1
# @package ChillDev\Bundle\FileManagerBundle
-->

# Configuration

**ChillDevFileManagerBundle** allows you to define *disks* scopes to be accessible by file manager. Bundle's configuration namespace is `chilldev_filemanager`.

## Templating

In the world of **Symfony 2** most people use [Twig](http://twig.sensiolabs.org/) as templating engine. However we, at [Chillout Development](http://chilldev.pl/) can't understand that and [prefer good old PHP over Twig](http://wrzasq.pl/blog/chilldevviewhelpersbundle-php-templating-helpers-for-symfony-2.html). That's why default views for this bundle are provided in *PHP*. But we also believe that flexibility is one of the most important aspects of Symfony and good bundles should follow that manner. You can very easily switch templating engine used by this bundle by configuring it with:

```yaml
chilldev_filemanager:
    templating: "twig"
```

## Disks

Disk is a directory that will act as a wrapper for file manager - it will operate only within selected scopes (you can define multiple disks):

```yaml
chilldev_filemanager:
    disks:
        disk1_id:
            label: "Disk label"
            source: "/path/to/directory/"
        disk2_id:
            label: "Another disk label"
            source: "/path/to/another/directory/"
```

Configuration key is used as internal disk identifier. Disk *label* is displayed in web frontend and *source* is a path to directory to use as a wrapper.
