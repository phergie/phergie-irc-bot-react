<?php
/**
 * Phergie (http://phergie.org)
 *
 * @link http://github.com/phergie/phergie-irc-bot-react for the canonical source repository
 * @copyright Copyright (c) 2008-2014 Phergie Development Team (http://phergie.org)
 * @license http://phergie.org/license New BSD License
 * @package Phergie\Irc\Bot\React
 */

namespace Phergie\Irc\Bot\React;

use Phergie\Irc\Client\React\ClientInterface;
use Evenement\EventEmitterInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareInterface;

/**
 * Base class for plugins.
 *
 * @category Phergie
 * @package Phergie\Irc\Bot\React
 */
abstract class AbstractPlugin implements
    PluginInterface,
    LoggerAwareInterface,
    EventEmitterAwareInterface,
    ClientAwareInterface
{
    /**
     * Client for any adjustments the plugin may want to make
     *
     * @var ClientInterface
     */
    protected $client;

    /**
     * Event emitter used to register callbacks for IRC events of interest to
     * the plugin
     *
     * @var \Evenement\EventEmitterInterface
     */
    protected $emitter;

    /**
     * Logger for any debugging output the plugin may emit
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Sets the client for the plugin to use.
     *
     * @param ClientInterface $client
     */
    public function setClient(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Returns the client in use by the plugin.
     *
     * @return ClientInterface|null
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Sets the event emitter for the plugin to use.
     *
     * @param \Evenement\EventEmitterInterface $emitter
     */
    public function setEventEmitter(EventEmitterInterface $emitter)
    {
        $this->emitter = $emitter;
    }

    /**
     * Returns the event emitter in use by the plugin.
     *
     * @return \Evenement\EventEmitterInterface|null
     */
    public function getEventEmitter()
    {
        return $this->emitter;
    }

    /**
     * Sets the logger for the plugin to use.
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Returns the logger in use by the plugin.
     *
     * @return \Psr\Log\LoggerInterface|null
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Replaces bytes in a string that might cause it to be truncated or
     * otherwise misinterpreted by the server.
     *
     * @param string $string
     * @return string $string
     */
    public function escapeParam($string)
    {
        foreach (["\r\n", "\r", "\n"] as $badBytes) {
            if (false !== strpos($string, $badBytes)) {
                $string = str_replace($badBytes, " ", $string);
            }
        }

        if (false !== strpos($string, "\0")) {
            $string = str_replace("\0", "", $string);
        }

        return $string;
    }
}
