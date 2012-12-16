<!---
# This file is part of the ChillDev FileManager bundle.
#
# @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
# @copyright 2012 © by Rafał Wrzeszcz - Wrzasq.pl.
# @version 0.0.1
# @since 0.0.1
# @package ChillDev\Bundle\FileManagerBundle
-->

# Installation

**Note:** this bundle requires **PHP 5.4**.

## Step 1: define composer dependency

Add `"chilldev/file-manager-bundle": "dev-master"` to your `composer.json` file (replace `dev-master` with your desired constraint if you want to use particular version). Then run `composer.phar install`.

## Step 2: include bundle in your kernel

Simply add following lines to your kernel:

```php
<?php

// in your use-s addd:
use ChillDev\Bundle\FileManagerBundle\ChillDevFileManagerBundle;

// in your kernel class:
public function registerBundles()
{
    // your bubdles list
    $bundles = [
        'ChillDevFileManagerBundle',
        …
    ];

    …
}
```

## Step 3: configuration

Finally, you need to define *disks* available in file manager - a *disk* is a path accessible by file manager (file manager can not operate outside wrapped scopes):

```
chilldev_filemanager:
    disks:
        disk_id:
            label: "Your filesystem"
            source: "/var/www/"
```

For details see [Configuration section](./configuration.md).
