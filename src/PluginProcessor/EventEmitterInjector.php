<?php
/**
 * Phergie (http://phergie.org)
 *
 * @link http://github.com/phergie/phergie-irc-bot-react for the canonical source repository
 * @copyright Copyright (c) 2008-2014 Phergie Development Team (http://phergie.org)
 * @license http://phergie.org/license Simplified BSD License
 * @package Phergie\Irc\Bot\React
 */

namespace Phergie\Irc\Bot\React\PluginProcessor;

use Phergie\Irc\Bot\React\Bot;
use Phergie\Irc\Bot\React\EventEmitterAwareInterface;
use Phergie\Irc\Bot\React\PluginInterface;

/**
 * Plugin processor that injects the plugin with the bot's event emitter if
 * possible.
 *
 * @category Phergie
 * @package Phergie\Irc\Bot\React
 */
class EventEmitterInjector implements PluginProcessorInterface
{
    /**
     * Injects the bot's event emitter into the plugin if it implements
     * \Phergie\Irc\Bot\React\EventEmitterAwareInterface.
     *
     * @param \Phergie\Irc\Bot\React\PluginInterface $plugin Loaded plugin
     * @param \Phergie\Irc\Bot\React\Bot $bot Bot that loaded the plugin
     */
    public function process(PluginInterface $plugin, Bot $bot)
    {
        if ($plugin instanceof EventEmitterAwareInterface) {
            $plugin->setEventEmitter($bot->getClient());
        }
    }
}
