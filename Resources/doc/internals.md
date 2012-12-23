<!---
# This file is part of the ChillDev FileManager bundle.
#
# @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
# @copyright 2012 © by Rafał Wrzeszcz - Wrzasq.pl.
# @version 0.0.1
# @since 0.0.1
# @package ChillDev\Bundle\FileManagerBundle
-->

# Internals

## Development dependencies

In order to work on **ChillDevFileManagerBundle** you need to install a few more things then basic dependencies:

-   `phpunit/phpunit` - for running tests;
-   `squizlabs/php_codesniffer` - for coding style rules compilance checking;
-   *phpDocumentor* - for generating API documentation.

Most of them are defined in `composer.json` file, so running `composer.phar --dev install` will do the job. The only thing you need to install manualy is [phpDocumentor](http://www.phpdoc.org/) which does not have a valid **Composer** package yet. But you probably won't need it, since it's only used for publishing documentation. If you want to use it anyway, you will also need `php-xsl` extension.

## Coding style

Currently we just follow [PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md) coding rules.

## Makefile targets

This project utilizes `make` as primary build automation tool. It's `Makefile` defines following tasks:

-   `init` - initializes project by loading all **Git** submodules and installing dependencies with [Composer](http://getcomposer.org/);
-   `update` - updates dependencies with **Composer**;
-   `check` - performs syntax checking on all project files using `php -l`;
-   `lint` - checks coding standards compliance with [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer);
-   `tests` - runs all unit tests and generate coverage report with [phpUnit](http://www.phpunit.de/manual/current/en/index.html);
-   `documentation` - generates project API documentation with [phpDocumentor](http://www.phpdoc.org/).

There are also meta-targets:

-   `all` which executes `check`, `lint`, `tests` and `documentation` subsequently (it is run by default);
-   `ci` which executes `check`, `lint` and `tests` (set of QA targets).

## Continous integration

This project uses [Travis-CI](https://travis-ci.org/) as it's [continous intergation](https://travis-ci.org/chilloutdevelopment/ChillDevFileManagerBundle) environment. It is configured to evaluate `check`, `lint` and `tests` targets to ensure code matches quality standards.

## Templating engine

To simplify things we use `@Template` annotation from [SensioFrameworkExtraBundle](https://github.com/sensio/SensioFrameworkExtraBundle). It allows us to drop any direct template references from controller code. But there is one problem with this annotation - while it drops template reference from code it makes that implicit reference quite costant. Mainly it makes it impossible to switch between templating engines (if controller uses *Twig* and your application *PHP* templates you are doomed). *DelegatingEngine* from Symfony does not help here, since it just delegates templating to subsequent engine based on it's name, which is in our case already hardcoded.

Since `@Template` annotation is very handy and we didn't want to stop using it, while still having possiblity to switch between templating engines we created different kind of delegating engine - one that doesn't rely on template name, but on configuration and we called it `ConfigEngine`.

Because of that when creating actions for this bundle use:

```php
    /**
     * @Template(engine="config")
     */
    public function fooAction()
    {
        // action code here
        return $viewData;
    }
```

In this example annotation will still generate constant template reference to `Bundle:Controller:foo.html.config` and delegation will be done in rendering time. Depending on configuration it can for example forward rendering to `Bundle:Controller:foo.html.php` or `Bundle:Controller:foo.html.twig`.

# Why not…

## …use mount-style flow

Because it would be hard to apply. Mounts within web manager would not be applied to real filesystem so it could lead to conflicts. Imagine you use `/var/www/` as your root and define `/var/log/nginx/` to be mounted as `/logs/`. If you then create `/var/www/logs/` we could have a problem to solve - so at least for now it's better to avoid the problem as we have much more in ToDo list.

## …use [Gaufrette](https://github.com/KnpLabs/Gaufrette)

Because it works like a key-value store. It lacks tree traversing flow. It's nice if you need to support single directory abstraction, but not for file-managing purpose.
