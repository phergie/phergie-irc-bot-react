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
use Phergie\Irc\Bot\React\PluginProcessor\LoggerInjector;

/**
 * Tests for LoggerInjector.
 *
 * @category Phergie
 * @package Phergie\Irc\Bot\React
 */
class LoggerInjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests process() with a plugin that does not implement
     * LoggerAwareInterface.
     */
    public function testProcessWithNonLoggerAwarePlugin()
    {
        $bot = Phake::mock('\Phergie\Irc\Bot\React\Bot');
        $plugin = Phake::mock('\Phergie\Irc\Bot\React\PluginInterface');
        Phake::verifyNoFurtherInteraction($plugin);
        $processor = new LoggerInjector;
        $processor->process($plugin, $bot);
    }

    /**
     * Tests process() with a plugin that implements
     * LoggerAwareInterface.
     */
    public function testProcessWithLoggerAwarePlugin()
    {
        $logger = Phake::mock('\Psr\Log\LoggerInterface');
        $bot = Phake::mock('\Phergie\Irc\Bot\React\Bot');
        Phake::when($bot)->getLogger()->thenReturn($logger);
        $plugin = Phake::mock('\Phergie\Irc\Bot\React\AbstractPlugin');
        $processor = new LoggerInjector;
        $processor->process($plugin, $bot);
        Phake::verify($plugin)->setLogger($logger);
    }
}
