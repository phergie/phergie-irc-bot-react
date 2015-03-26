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
use Phergie\Irc\Bot\React\PluginProcessor\EventEmitterInjector;

/**
 * Tests for EventEmitterInjector.
 *
 * @category Phergie
 * @package Phergie\Irc\Bot\React
 */
class EventEmitterInjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests process() with a plugin that does not implement
     * EventEmitterAwareInterface.
     */
    public function testProcessWithNonEventEmitterAwarePlugin()
    {
        $bot = Phake::mock('\Phergie\Irc\Bot\React\Bot');
        $plugin = Phake::mock('\Phergie\Irc\Bot\React\PluginInterface');
        Phake::verifyNoFurtherInteraction($plugin);
        $processor = new EventEmitterInjector;
        $processor->process($plugin, $bot);
    }

    /**
     * Tests process() with a plugin that implements
     * EventEmitterAwareInterface.
     */
    public function testProcessWithEventEmitterAwarePlugin()
    {
        $client = Phake::mock('\Phergie\Irc\Client\React\ClientInterface');
        $bot = Phake::mock('\Phergie\Irc\Bot\React\Bot');
        Phake::when($bot)->getClient()->thenReturn($client);
        $plugin = Phake::mock('\Phergie\Irc\Bot\React\AbstractPlugin');
        $processor = new EventEmitterInjector;
        $processor->process($plugin, $bot);
        Phake::verify($plugin)->setEventEmitter($client);
    }
}
