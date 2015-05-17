<!---
# This file is part of the ChillDev FileManager bundle.
#
# @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
# @copyright 2012 - 2014 © by Rafał Wrzeszcz - Wrzasq.pl.
# @version 0.1.4
# @since 0.0.1
# @package ChillDev\Bundle\FileManagerBundle
-->

# ChillDev FileManager bundle

**ChillDevFileManagerBundle** is a **Symfony2 bundle** that provides file management features from your web application.

[![Build Status](https://travis-ci.org/chilloutdevelopment/ChillDevFileManagerBundle.png)](https://travis-ci.org/chilloutdevelopment/ChillDevFileManagerBundle)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/chilloutdevelopment/ChillDevFileManagerBundle/badges/quality-score.png?s=87dd1a10b9e28fe445d9695ef8c9ca6444fe3458)](https://scrutinizer-ci.com/g/chilloutdevelopment/ChillDevFileManagerBundle/)
[![Coverage Status](https://coveralls.io/repos/chilloutdevelopment/ChillDevFileManagerBundle/badge.png?branch=develop)](https://coveralls.io/r/chilloutdevelopment/ChillDevFileManagerBundle)
[![Dependency Status](https://www.versioneye.com/php/chilldev:file-manager-bundle/badge.png)](https://www.versioneye.com/php/chilldev:file-manager-bundle)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/25d48a5e-3640-44ba-b1ff-ee46c25d3b0e/mini.png)](https://insight.sensiolabs.com/projects/25d48a5e-3640-44ba-b1ff-ee46c25d3b0e)

# Installation

This bundle is provided as [Composer package](https://packagist.org/packages/chilldev/file-manager-bundle). To install it simply add following dependency definition to your `composer.json` file:

```
"chilldev/file-manager-bundle": "dev-master"
```

Replace `dev-master` with different constraint if you want to use specific version.

If you are using modern version of Symfony2, use `dev-develop` version instead of `dev-master`. (But this version don't tested properly yet)

**Note:** This bundle requires **PHP 5.4**.

**Note:** You also need `default` templating engine installed and make sure all required bundles are also loaded in your application. See [installation instructions](https://github.com/chilloutdevelopment/ChillDevFileManagerBundle/tree/master/Resources/doc/installation.md) for details about that.

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

## Frontend

In general, **ChillDevFileManagerBundle** is end-user ready (or at least should be) component. However there can be some issues related to frontend presentation that you can be interested in (mainly JavaScript-related). For list of things you need to know to get best experience of this bundle UI see [usage documentation](https://github.com/chilloutdevelopment/ChillDevFileManagerBundle/tree/master/Resources/doc/usage.md).

## Actions handlers

Another aspect which you may be interested in is implementing own file actions. You can easily extend **ChillDevFileManagerBundle** to allow additional file actions (even filetype-specific). You can bind your own action handler to specific MIME type by registring your instances of `HandlerInterface` interface:

```php
class MyHandler implements HandlerInterface
{
    public function getLabel()
    {
        return 'My action';
    }

    public function supports($mimeType)
    {
        return preg_match('#^image/#', $mimeType) > 0;
    }

    public function handle(Request $request, Disk $disk, $path)
    {
        // handle action request
    }
}
```

You can read [more detailed info here](https://github.com/chilloutdevelopment/ChillDevFileManagerBundle/tree/master/Resources/doc/usage/actions-handlers.md).

# Extras

As a bonus, you can integrate **ChillDevFileManagerBundle** with [**SonataAdminBundle**](https://github.com/sonata-project/SonataAdminBundle). You can read about how to do that in [this section](https://github.com/chilloutdevelopment/ChillDevFileManagerBundle/tree/master/Resources/doc/extras.md).

# Resources

-   [Source documentation](https://github.com/chilloutdevelopment/ChillDevFileManagerBundle/tree/master/Resources/doc/index.md)
-   [GitHub page with API documentation](http://chilloutdevelopment.github.io/ChillDevFileManagerBundle)
-   [Issues tracker](https://github.com/chilloutdevelopment/ChillDevFileManagerBundle/issues)
-   [Packagist package](https://packagist.org/packages/chilldev/file-manager-bundle)
-   [Chillout Development @ GitHub](https://github.com/chilloutdevelopment)
-   [Chillout Development @ Facebook](http://www.facebook.com/chilldev)
-   [Post on Wrzasq.pl](http://wrzasq.pl/blog/chilldevfilemanagerbundle-filemanager-frontend-bundle-for-symfony-2.html)

# Contributing

Do you want to help improving this project? Simply *fork* it and post a pull request. You can do everything on your own, you don't need to ask if you can, just do all the awesome things you want!

This project is published under [MIT license](https://github.com/chilloutdevelopment/ChillDevFileManagerBundle/tree/master/LICENSE).

# Authors

**ChillDevFileManagerBundle** is brought to you by [Chillout Development](http://chilldev.pl).

List of contributors:

-   [Rafał "Wrzasq" Wrzeszcz](https://github.com/rafalwrzeszcz) ([wrzasq.pl](http://wrzasq.pl)).
