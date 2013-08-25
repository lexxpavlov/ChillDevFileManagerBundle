<?php

/**
 * This file is part of the ChillDev FileManager bundle.
 *
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.2
 * @since 0.1.2
 * @package ChillDev\Bundle\FileManagerBundle
 */

namespace ChillDev\Bundle\FileManagerBundle\Tests\Block;

use ChillDev\Bundle\FileManagerBundle\Block\DisksList;
use ChillDev\Bundle\FileManagerBundle\Tests\BaseManagerTest;

use Sonata\BlockBundle\Block\BlockContext;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright 2013 © by Rafał Wrzeszcz - Wrzasq.pl.
 * @version 0.1.2
 * @since 0.1.2
 * @package ChillDev\Bundle\FileManagerBundle
 */
class DisksListTest extends BaseManagerTest
{
    /**
     * @test
     * @version 0.1.2
     * @since 0.1.2
     */
    public function execute()
    {
        // create mock objects
        $templating = $this->getTemplatingMock(['renderResponse']);
        $response = new Response();
        $template = 'foo';
        $context = new BlockContext($this->getMock('Sonata\\BlockBundle\\Model\\BlockInterface'), ['template' => $template]);

        $block = new DisksList('', $templating, $this->manager);

        // set expectations
        $templating->expects($this->once())
            ->method('renderResponse')
            ->with(
                $this->equalTo($template),
                $this->arrayHasKey('disks'),
                $this->identicalTo($response)
            )
            ->will($this->returnValue($response));

        $this->assertSame($response, $block->execute($context, $response), 'DisksList::execute() should return rendered response.');
    }

    /**
     * @test
     * @version 0.1.2
     * @since 0.1.2
     */
    public function setDefaultSettings()
    {
        $block = new DisksList('', $this->getTemplatingMock(), $this->manager);

        $optionsResolver = new OptionsResolver();
        $block->setDefaultSettings($optionsResolver);

        $options = $optionsResolver->resolve();
        $this->assertInternalType('string', $options['template'], 'DisksList::setDefaultSettings() should set default template.');
    }

    /**
     * @test
     * @version 0.1.2
     * @since 0.1.2
     */
    public function coverage()
    {
        $block = new DisksList('', $this->getTemplatingMock(), $this->manager);
        // just to make sure no errors occur
        $block->validateBlock(
            $this->getMock('Sonata\\AdminBundle\\Validator\\ErrorElement', [], [], '', false),
            $this->getMock('Sonata\\BlockBundle\\Model\\BlockInterface')
        );
        $block->buildEditForm(
            $this->getMock('Sonata\\AdminBundle\\Form\\FormMapper', [], [], '', false),
            $this->getMock('Sonata\\BlockBundle\\Model\\BlockInterface')
        );
        $this->assertInternalType('string', $block->getName(), 'DisksList::getName() should return block title.');
    }

    /**
     * @param string[] $methods
     * @return Symfony\Bundle\FrameworkBundle\Templating\EngineInterface
     * @version 0.1.2
     * @since 0.1.2
     */
    protected function getTemplatingMock(array $methods = [])
    {
        $methods = array_merge($methods, ['render', 'exists', 'supports', 'renderResponse']);
        return $this->getMock('Symfony\\Bundle\\FrameworkBundle\\Templating\\EngineInterface', array_unique($methods));
    }
}
