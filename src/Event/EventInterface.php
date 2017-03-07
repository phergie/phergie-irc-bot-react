<?php
/**
 * Phergie (http://phergie.org)
 *
 * @link http://github.com/phergie/phergie-irc-bot-react for the canonical source repository
 * @copyright Copyright (c) 2008-2013 Phergie Development Team (http://phergie.org)
 * @license http://phergie.org/license New BSD License
 * @package Phergie\Event
 */

namespace Phergie\Event;

/**
 * Interface for \Phergie\Event
 */
interface EventInterface
{
    /**
     * Accessor method to set message property
     *
     * @param string $message text of the message
     */
    public function setMessage($message);

    /**
     * Accessor method to retrieve message property
     *
     * @return string text of the message
     */
    public function getMessage();

    /**
     * Accessor method to set connection instance
     *
     * @param \Phergie\ConnectionInterface $connection
     */
    public function setConnection(\Phergie\ConnectionInterface);

    /**
     * Accessor method to retrieve connection instance
     *
     * @return \Phergie\ConnectionInterface
     */ 
    public function getConnection();

    /**
     * Accessor method to set parameter property
     *
     * @param array $params array of parameters
     */
    public function setParams(array $params);

    /**
     * Accessor method to retrieve parameter property
     *
     * @return array array of parameters
     */
    public function getParams();
}
