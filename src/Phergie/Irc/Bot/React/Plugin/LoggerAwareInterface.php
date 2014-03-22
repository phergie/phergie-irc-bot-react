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

use Psr\Log\LoggerAwareInterface as BaseLoggerAwareInterface;

/**
 * Interface for retrieving the optional logger dependency of plugins.
 *
 * @category Phergie
 * @package Phergie\Irc\Bot\React
 */
interface LoggerAwareInterface extends BaseLoggerAwareInterface
{
    /**
     * Returns the logger in use by the plugin.
     *
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger();
}
