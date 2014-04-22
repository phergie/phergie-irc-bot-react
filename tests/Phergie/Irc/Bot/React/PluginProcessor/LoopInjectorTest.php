<?php
/**
 * Phergie (http://phergie.org)
 *
 * @link http://github.com/phergie/phergie-irc-bot-react for the canonical source repository
 * @copyright Copyright (c) 2008-2014 Phergie Development Team (http://phergie.org)
 * @license http://phergie.org/license New BSD License
 * @package Phergie\Irc\Bot\React
 */

namespace Phergie\Irc\Bot\React\PluginProcessor;

use Phake;

/**
 * Tests for LoopInjector.
 *
 * @category Phergie
 * @package Phergie\Irc\Bot\React
 */
class LoopInjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests process() with a plugin that does not implement
     * LoopAwareInterface.
     */
    public function testProcessWithNonLoopAwarePlugin()
    {
        $bot = Phake::mock('\Phergie\Irc\Bot\React\Bot');
        $plugin = Phake::mock('\Phergie\Irc\Bot\React\PluginInterface');
        Phake::verifyNoFurtherInteraction($plugin);
        $processor = new LoopInjector;
        $processor->process($plugin, $bot);
    }

    /**
     * Tests process() with a plugin that implements
     * LoopAwareInterface.
     */
    public function testProcessWithLoopAwarePlugin()
    {
        $loop = Phake::mock('\React\EventLoop\LoopInterface');
        $client = Phake::mock('\Phergie\Irc\Client\React\Client');
        Phake::when($client)->getLoop()->thenReturn($loop);
        $bot = Phake::mock('\Phergie\Irc\Bot\React\Bot');
        Phake::when($bot)->getClient()->thenReturn($client);
        $plugin = Phake::mock('\Phergie\Irc\Bot\React\AbstractPlugin');
        $processor = new LoopInjector;
        $processor->process($plugin, $bot);
        Phake::verify($plugin)->setLoop($loop);
    }
}
