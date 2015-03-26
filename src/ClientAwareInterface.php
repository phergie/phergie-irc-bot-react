<?php
/**
 * Phergie (http://phergie.org)
 *
 * @link http://github.com/phergie/phergie-irc-bot-react for the canonical source repository
 * @copyright Copyright (c) 2008-2015 Phergie Development Team (http://phergie.org)
 * @license http://phergie.org/license Simplified BSD License
 * @package Phergie\Irc\Bot\React
 */

namespace Phergie\Irc\Bot\React;

use Phergie\Irc\Client\React\ClientInterface;

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
     * @param ClientInterface $client
     */
    public function setClient(ClientInterface $client);
}
