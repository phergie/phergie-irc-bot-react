<?php
/**
 * Phergie (http://phergie.org)
 *
 * @link http://github.com/phergie/phergie-irc-bot-react for the canonical source repository
 * @copyright Copyright (c) 2008-2014 Phergie Development Team (http://phergie.org)
 * @license http://phergie.org/license New BSD License
 * @package Phergie\Irc\Bot\React
 */

namespace Phergie\Irc\Bot\React\Plugin;

/**
 * Interface for injecting required configuration into plugins using the
 * constructor.
 *
 * @category Phergie
 * @package Phergie\Irc\Bot\React
 */
interface ConstructorConfigurableInterface extends ConfigurableInterface
{
    /**
     * Sets the configuration for the plugin to use.
     *
     * @param array $config
     */
    public function __construct(array $config);
}
