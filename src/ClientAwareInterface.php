<?php
/**
 * Phergie (http://phergie.org)
 *
 * @link http://github.com/phergie/phergie-irc-bot-react for the canonical source repository
 * @copyright Copyright (c) 2008-2015 Phergie Development Team (http://phergie.org)
 * @license http://phergie.org/license New BSD License
 * @package Phergie\Irc\Bot\React
 */

namespace Phergie\Irc\Bot\React;

use Phergie\Irc\Client\React\Client;

/**
 * Interface for injection of the client.
 *
 * @category Phergie
 * @package Phergie\Irc\Bot\React
 */
interface ClientAwareInterface
{
    /**
     * Sets the client for the implementing class to use.
     *
     * @param Client $client
     */
    public function setClient(Client $client);
}
