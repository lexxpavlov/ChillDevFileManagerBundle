<!---
# This file is part of the ChillDev FileManager bundle.
#
# @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
# @copyright 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
# @version 0.1.0
# @since 0.0.3
# @package ChillDev\Bundle\FileManagerBundle
-->

# Usage

In general, **ChillDevFileManagerBundle** is end-user ready (or at least should be) component. However there can be some issues related to frontend presentation that you can be interested in.

## JavaScript

First of all, any JavaScript code attached to this bundle uses [http://prototypejs.org/](Prototype JavaScript framework).

Second thing is, that bundle itself doesn't load any JavaScript files on it's own. You have to include it's `.js` files yourself because:

1.  we know you may use different library then **Prototype** (possibly **jQuery**), then you can implement your own rich-UI,
1.  you most probably use **Composer** to manage dependencies and have your own deployment cycle for assets, so we leave it up to you how to deal with bundle static files,
1.  it's hard to attach JavaScript dependencies in Composer-based projects.

Before dealing with any JavaScript you should probably run `assets:install` command in your Symfony's project console to install this bundle assets in your public directory.

Here is a list of all JavaScript-based UI improvements:

### Delete confirmation

File for this feature: [Resources/public/javascript/confirm.js](https://github.com/chilloutdevelopment/ChillDevFileManagerBundle/blob/master/Resources/public/javascript/confirm.js).

This file causes confirmation box to appear before any file deletion. Each form with class `confirm-required` will be handled by event listener that will use form's `data-confirm` attribute to display confirmation dialog box.

To add load this file directly, using **ChillDevViewHelpersBundle** use:
```php
$view['script']->add($view['assets']->getUrl('bundles/chilldevfilemanager/javascript/confirm.js'));
```
