<!---
# This file is part of the ChillDev FileManager bundle.
#
# @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
# @copyright 2012 - 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
# @version 0.1.1
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

## Step 4: configuration

Finally, you need to define *disks* available in file manager - a *disk* is a path accessible by file manager (file manager can not operate outside wrapped scopes):

```yaml
chilldev_filemanager:
    disks:
        disk_id:
            label: "Your filesystem"
            source: "/var/www/"
```

For details see [Configuration section](./configuration.md).

## Extra: default templating engine

Another thing you need to do is enabling *default* templating engine. This is how we achive templating engine switch to make this bundle interchangeable with systems that use different templating engines. We do not provide any automated way of installing any engine with name `default`, to make your system more flexible. If you don't have such engine, take a look at our [ChillDevProxyTemplatingBundle](https://chilloutdevelopment/ChillDevProxyTemplatingBundle).
