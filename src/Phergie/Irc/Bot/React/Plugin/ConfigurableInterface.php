<?php
/**
 * Phergie (http://phergie.org)
 *
 * @link http://github.com/phergie/phergie-irc-bot-react for the canonical source repository
 * @copyright Copyright (c) 2008-2013 Phergie Development Team (http://phergie.org)
 * @license http://phergie.org/license New BSD License
 * @package Phergie\Irc\Bot\React
 */

namespace Phergie\Irc\Bot\React\Plugin;

/**
 * Interface for retrieving configuration information from a plugin.
 *
 * @category Phergie
 * @package Phergie\Irc\Bot\React
 */
interface ConfigurableInterface
{
    /**
     * Returns the configuration in use by the plugin.
     *
     * @return array
     */
    public function getConfig();
}
