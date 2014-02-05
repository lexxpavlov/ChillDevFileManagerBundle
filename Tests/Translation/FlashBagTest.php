<?php

/**
 * This file is part of the ChillDev FileManager bundle.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2014 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.3
 * @since 0.1.3
 * @package ChillDev\Bundle\FileManagerBundle
 */

namespace ChillDev\Bundle\FileManagerBundle\Tests\Controller;

use PHPUnit_Framework_TestCase;

use ChillDev\Bundle\FileManagerBundle\Translation\FlashBag;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBag as FlashContainer;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Loader\ArrayLoader;

/**
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2014 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.3
 * @since 0.1.3
 * @package ChillDev\Bundle\FileManagerBundle
 */
class FlashBagTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @version 0.1.3
     * @since 0.1.3
     */
    public function add()
    {
        // pre-defined values
        $source = 'test "%file%" message';
        $translation = 'translated into "%file%"';
        $params = ['%file%' => 'test'];
        $type = 'done';

        // test mocks
        $flashBag = new FlashContainer();
        $translator = new Translator('pl_PL', new MessageSelector());
        $translator->setFallbackLocales(['pl']);
        $translator->addLoader('array', new ArrayLoader());
        $translator->addResource('array', [$source => $translation], 'pl');

        // our object
        $test = new FlashBag($flashBag, $translator);
        $test->add($type, $source, $params);

        // flash message properties
        $flashes = $flashBag->peekAll();
        $this->assertArrayHasKey($type, $flashes, 'FlashBag::add() should set flash message of given type.');
        $this->assertCount(1, $flashes[$type], 'FlashBag::add() should set flash message of given type.');
        $this->assertEquals('translated into "test"', $flashes[$type][0], 'FlashBag::add() should translate flash message.');
    }
}
