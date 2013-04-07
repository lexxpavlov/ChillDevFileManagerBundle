<!---
# This file is part of the ChillDev FileManager bundle.
#
# @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
# @copyright 2012 - 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
# @version 0.1.1
# @since 0.0.1
# @package ChillDev\Bundle\FileManagerBundle
-->

# ChillDev FileManager bundle

**ChillDevFileManagerBundle** is a **Symfony2 bundle** that provides file management features from your web application.

[![Build Status](https://travis-ci.org/chilloutdevelopment/ChillDevFileManagerBundle.png)](https://travis-ci.org/chilloutdevelopment/ChillDevFileManagerBundle)

# Installation

This bundle is provided as [Composer package](https://packagist.org/packages/chilldev/file-manager-bundle). To install it simply add following dependency definition to your `composer.json` file:

```
"chilldev/file-manager-bundle": "dev-master"
```

Replace `dev-master` with different constraint if you want to use specific version.

**Note:** This bundle requires **PHP 5.4**.

**Note:** You also need `default` templating engine installed. See [installation instructions](https://github.com/chilloutdevelopment/ChillDevFileManagerBundle/tree/master/Resources/doc/installation.md) for details about that.

# Configuration

In order to use this bundle, load it in your kernel:

```php
<?php

use ChillDev\Bundle\FileManagerBundle\ChillDevFileManagerBundle;

use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    public function registerBundles()
    {
        $bundles = [
            new ChillDevFileManagerBundle(),
        ];
    }
}
```

Include bundle routes:

```yaml
ChillDevFileManagerBundle:
    resource: "@ChillDevFileManagerBundle/Controller/"
    type: "annotation"
    prefix: "/filemanager"
```

And then configure your disks:

```yaml
chilldev_filemanager:
    disks:
        disk_id:
            label: "Your filesystem"
            source: "/var/www/"
```

See [configuration options](https://github.com/chilloutdevelopment/ChillDevFileManagerBundle/tree/master/Resources/doc/configuration.md) for details.

# Usage

In general, **ChillDevFileManagerBundle** is end-user ready (or at least should be) component. However there can be some issues related to frontend presentation that you can be interested in (mainly JavaScript-related). For list of things you need to know to get best experience of this bundle UI see [usage documentation](https://github.com/chilloutdevelopment/ChillDevFileManagerBundle/tree/master/Resources/doc/usage.md).

# Resources

-   [Source documentation](https://github.com/chilloutdevelopment/ChillDevFileManagerBundle/tree/master/Resources/doc/index.md)
-   [GitHub page with API documentation](http://chilloutdevelopment.github.io/ChillDevFileManagerBundle)
-   [Travis CI](https://travis-ci.org/chilloutdevelopment/ChillDevFileManagerBundle)
-   [Issues tracker](https://github.com/chilloutdevelopment/ChillDevFileManagerBundle/issues)
-   [Packagist package](https://packagist.org/packages/chilldev/file-manager-bundle)
-   [Chillout Development @ GitHub](https://github.com/chilloutdevelopment)
-   [Chillout Development @ Facebook](http://www.facebook.com/chilldev)
-   [Post on Wrzasq.pl](http://wrzasq.pl/blog/chilldevfilemanagerbundle-filemanager-frontend-bundle-for-symfony-2.html)

# Contributing

Do you want to help improving this project? Simply *fork* it and post a pull request. You can do everything on your own, you don't need to ask if you can, just do all the awesome things you want!

This project is published under [MIT license](https://github.com/chilloutdevelopment/ChillDevFileManagerBundle/LICENSE).

# Authors

**ChillDevFileManagerBundle** is brought to you by [Chillout Development](http://chilldev.pl).

List of contributors:

-   [Rafał "Wrzasq" Wrzeszcz](https://github.com/rafalwrzeszcz) ([wrzasq.pl](http://wrzasq.pl)).
