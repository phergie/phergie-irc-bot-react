<?php
/**
 * Phergie (http://phergie.org)
 *
 * @link http://github.com/phergie/phergie-irc-bot-react for the canonical source repository
 * @copyright Copyright (c) 2008-2014 Phergie Development Team (http://phergie.org)
 * @license http://phergie.org/license Simplified BSD License
 * @package Phergie\Irc\Bot\React
 */

namespace Phergie\Irc\Tests\Bot\React\PluginProcessor;

use Phake;
use Phergie\Irc\Bot\React\PluginInterface;
use Phergie\Irc\Bot\React\PluginProcessor\LoopInjector;
use Phergie\Irc\Client\React\LoopAwareInterface;
use React\EventLoop\LoopInterface;

/**
 * Tests for LoopInjector.
 *
 * @category Phergie
 * @package Phergie\Irc\Bot\React
 */
class LoopInjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Data provider for testProcessWhenPluginDoesNotReceiveLoop().
     *
     * @return array
     */
    public function dataProviderProcessWhenPluginDoesNotReceiveLoop()
    {
        $data = array();

        // Neither plugin nor client implements interface
        $data[] = array(
            Phake::mock('\Phergie\Irc\Bot\React\PluginInterface'),
            Phake::mock('\Phergie\Irc\Client\React\ClientInterface'),
        );

        // Plugin implements interface, client does not
        $data[] = array(
            Phake::mock('\Phergie\Irc\Bot\React\PluginInterface'),
            Phake::mock('\Phergie\Irc\Client\React\LoopAccessorInterface'),
        );

        // Client implements interface, plugin does not
        $data[] = array(
            Phake::mock('\Phergie\Irc\Bot\React\AbstractPlugin'),
            Phake::mock('\Phergie\Irc\Client\React\ClientInterface'),
        );

        return $data;
    }

    /**
     * Tests process() under circumstances in which the plugin will receive the
     * loop.
     *
     * @dataProvider dataProviderProcessWhenPluginDoesNotReceiveLoop
     */
    public function testProcessWhenPluginDoesNotReceiveLoop($plugin, $client)
    {
        $bot = Phake::mock('\Phergie\Irc\Bot\React\Bot');
        Phake::when($bot)->getClient()->thenReturn($client);
        Phake::verifyNoFurtherInteraction($plugin);
        $processor = new LoopInjector;
        $processor->process($plugin, $bot);
    }

    /**
     * Tests process() under circumstances in which the plugin will receive the
     * loop.
     */
    public function testProcessWhenPluginReceivesLoop()
    {
        $loop = Phake::mock('\React\EventLoop\LoopInterface');
        $client = Phake::mock('\Phergie\Irc\Client\React\Client');
        Phake::when($client)->getLoop()->thenReturn($loop);
        $bot = Phake::mock('\Phergie\Irc\Bot\React\Bot');
        Phake::when($bot)->getClient()->thenReturn($client);
        $plugin = Phake::mock('\Phergie\Irc\Tests\Bot\React\PluginProcessor\LoopAwarePlugin');
        $processor = new LoopInjector;
        $processor->process($plugin, $bot);
        Phake::verify($plugin)->setLoop($loop);
    }
}

/**
 * Loop-aware plugin implementation.
 */
class LoopAwarePlugin implements PluginInterface, LoopAwareInterface
{
    public function getSubscribedEvents() { }
    public function setLoop(LoopInterface $loop) { }
}
