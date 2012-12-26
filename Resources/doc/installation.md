<!---
# This file is part of the ChillDev FileManager bundle.
#
# @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
# @copyright 2012 © by Rafał Wrzeszcz - Wrzasq.pl.
# @version 0.0.2
# @since 0.0.1
# @package ChillDev\Bundle\FileManagerBundle
-->

# Installation

**Note:** this bundle requires **PHP 5.4**.

## Step 1: define composer dependency

Add `"chilldev/file-manager-bundle": "dev-master"` to your `composer.json` file (replace `dev-master` with your desired constraint if you want to use particular version). Then run `composer.phar install`.

**Note:** `dev-master` is a reference to `master` branch which is **stable** - to use current development branch with latest updates use `dev-develop`.

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

## Step 3: routing

You also need to make filemanager accessible through your router. All bundle routes are defined with annotations, so you need something like this in your router configuration:

```yaml
ChillDevFileManagerBundle:
    resource: "@ChillDevFileManagerBundle/Controller/"
    type: "annotation"
    prefix: "/filemanager"
```

**Note:** currently file manager does not provide any access control features, so it's best to set up firewall for handling file manager access since it's a very sensitive tool that allows to do literaly anything with your physical files on server.

## Step 4: enabling templating switch

Another thing you need to do is enabling *config* templating engine. It is our proxy templating engine that allows switching templating engine from configuration level (it is not the same as *DelegatingEngine* from Symfony):

```yaml
framework:
    templating:
        engines:
            - "twig"
            - "php"
            - "config"
```

Interested in what is this templating engine? [See internals section.](./internals.md)

## Step 5: configuration

Finally, you need to define *disks* available in file manager - a *disk* is a path accessible by file manager (file manager can not operate outside wrapped scopes):

```yaml
chilldev_filemanager:
    disks:
        disk_id:
            label: "Your filesystem"
            source: "/var/www/"
```

For details see [Configuration section](./configuration.md).
