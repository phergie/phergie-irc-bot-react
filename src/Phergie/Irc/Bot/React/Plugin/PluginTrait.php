<?php
/**
 * Phergie (http://phergie.org)
 *
 * @link http://github.com/phergie/phergie-irc-bot-react for the canonical source repository
 * @copyright Copyright (c) 2008-2013 Phergie Development Team (http://phergie.org)
 * @license http://phergie.org/license New BSD License
 * @package Phergie\Irc\Bot\React\Plugin
 */

namespace Phergie\Irc\Bot\React\Plugin;

/**
 * Trait that provides all commonly needed functionality for a plugin
 * implementation.
 *
 * Classes using this trait will need to handle implementing PluginInterface
 * and ConfigurableInterface. The latter can be handled by implementing either
 * AccessorConfigurationInterface or ConstructorConfigurationInterface or by
 * using either of AccessorConfigurationTrait or ConstructorConfigurationTrait.
 *
 * @category Phergie
 * @package Phergie\Irc\Bot\React
 */
trait PluginTrait implements PluginInterface, ConfigurableInterface
{
    use EmittableTrait;
    use LoggableTrait;
    use PluginAwareTrait;
}
