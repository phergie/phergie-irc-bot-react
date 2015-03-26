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
use Phergie\Irc\Bot\React\PluginInterface;

/**
 * Interface for an operation to be performed on a plugin once it's been
 * loaded.
 *
 * @category Phergie
 * @package Phergie\Irc\Bot\React
 */
interface PluginProcessorInterface
{
    /**
     * Performs on operation on a loaded plugin.
     *
     * @param \Phergie\Irc\Bot\React\PluginInterface $plugin Loaded plugin
     * @param \Phergie\Irc\Bot\React\Bot $bot Bot that loaded the plugin
     */
    public function process(PluginInterface $plugin, Bot $bot);
}
