<?php
/**
 * Phergie (http://phergie.org)
 * @copyright Copyright (c) 2008-2014 Phergie Development Team (http://phergie.org)
 * @license http://phergie.org/license Simplified BSD License
 * @package Phergie\Irc\Bot\React
 */

namespace Phergie\Irc\Bot\React;

use Phergie\Irc\Event\CtcpEvent;
use Phergie\Irc\Event\UserEvent;
use Phergie\Irc\Event\UserEventInterface;

/**
 * Queue to contain commands issued by plugins to be sent to servers so as to
 * allow for manipulation of those commands by plugins prior to their
 * transmission.
 *
 * @category Phergie
 * @package Phergie\Irc\Bot\React
 */
class EventQueue implements EventQueueInterface
{
    /**
     * Internal priority queue.
     *
     * @var \Phergie\Irc\Bot\React\EventQueueInternal
     */
    protected $queue;

    /**
     * Prefix for queued messages
     *
     * @var string
     */
    protected $prefix;

    /**
     * Enumerated array of commands in priority order
     *
     * @var array
     */
    protected $priorities;

    /**
     * Track the last timestamp used for priority so we can avoid duplicate values
     *
     * @var int
     */
    protected $lastTimestamp = 0;

    /**
     * Initializes the list of event priorities.
     */
    public function __construct()
    {
        $this->queue = new EventQueueInternal;

        $this->priorities = $this->getPriorities();
    }

    /**
     * Allows iteration over the event queue.
     *
     * @return \Phergie\Irc\Bot\React\EventQueueInternal
     */
    public function getIterator()
    {
        return clone $this->queue;
    }

    /**
     * Wrapper for the queue's internal count method.
     *
     * @return int
     */
    public function count()
    {
        return $this->queue->count();
    }

    /**
     * Removes and returns an event from the front of the queue.
     *
     * @return \Phergie\Irc\Event\EventInterface|null Removed event or null if
     *         the queue is empty
     */
    public function extract()
    {
        if ($this->queue->isEmpty()) {
            return null;
        }
        return $this->queue->extract();
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->setPrefix().
     *
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * Returns the priority of a specified command.
     *
     * @param string $command
     * @param array $params Unused, intended for use by subclasses
     * @return \Phergie\Irc\Bot\React\EventQueuePriority
     */
    protected function getPriority($command, array $params)
    {
        $priority = new EventQueuePriority;
        $priority->value = $this->priorities[$command];
        $priority->timestamp = (int) (microtime(true) * 10000);
        if ($priority->timestamp <= $this->lastTimestamp) {
            $priority->timestamp = $this->lastTimestamp + 1;
        }
        $this->lastTimestamp = $priority->timestamp;
        return $priority;
    }

    /**
     * Enqueues a new event.
     *
     * @param \Phergie\Irc\Event\UserEventInterface
     * @param string $command
     * @param array $params
     */
    protected function queueRequest(UserEventInterface $event, $command, array $params)
    {
        $event->setPrefix($this->prefix);
        $event->setCommand($command);
        $event->setParams(array_filter($params));
        $this->queue->insert($event, $this->getPriority($command, $params));
    }

    /**
     * Enqueues a new IRC event.
     *
     * @param string $command
     * @param array $params
     */
    protected function queueIrcRequest($command, array $params = array())
    {
        $this->queueRequest(new UserEvent, $command, $params);
    }

    /**
     * Enqueues a new CTCP event.
     *
     * @param string $command IRC command
     * @param string $ctcpCommand CTCP command
     * @param array $params Command parameters
     */
    protected function queueCtcpEvent($command, $ctcpCommand, array $params = array())
    {
        $event = new CtcpEvent;
        $event->setCtcpCommand($ctcpCommand);
        $this->queueRequest($event, $command, $params);
    }

    /**
     * Enqueues a new CTCP request.
     *
     * @param string $ctcpCommand CTCP command
     * @param array $params Command parameters
     */
    protected function queueCtcpRequest($ctcpCommand, array $params = array())
    {
        $this->queueCtcpEvent('PRIVMSG', $ctcpCommand, $params);
    }

    /**
     * Enqueues a new CTCP response.
     *
     * @param string $ctcpCommand CTCP command
     * @param array $params CTCP parameters
     */
    protected function queueCtcpResponse($ctcpCommand, array $params = array())
    {
        $this->queueCtcpEvent('NOTICE', $ctcpCommand, $params);
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ircPass().
     *
     * @param string $password
     */
    public function ircPass($password)
    {
        $this->queueIrcRequest('PASS', array($password));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ircNick().
     *
     * @param string $nickname
     * @param int $hopcount
     */
    public function ircNick($nickname, $hopcount = null)
    {
        $this->queueIrcRequest('NICK', array($nickname, $hopcount));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ircUser().
     *
     * @param string $username
     * @param string $hostname
     * @param string $servername
     * @param string $realname
     */
    public function ircUser($username, $hostname, $servername, $realname)
    {
        $this->queueIrcRequest('USER', array($username, $hostname, $servername, $realname));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ircServer().
     *
     * @param string $servername
     * @param int $hopcount
     * @param string $info
     */
    public function ircServer($servername, $hopcount, $info)
    {
        $this->queueIrcRequest('SERVER', array($servername, $hopcount, $info));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ircOper().
     *
     * @param string $user
     * @param string $password
     */
    public function ircOper($user, $password)
    {
        $this->queueIrcRequest('OPER', array($user, $password));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ircQuit().
     *
     * @param string $message
     */
    public function ircQuit($message = null)
    {
        $this->queueIrcRequest('QUIT', array($message));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ircSquit().
     *
     * @param string $server
     * @param string $comment
     */
    public function ircSquit($server, $comment)
    {
        $this->queueIrcRequest('SQUIT', array($server, $comment));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ircJoin().
     *
     * @param string $channels
     * @param string $keys
     */
    public function ircJoin($channels, $keys = null)
    {
        $this->queueIrcRequest('JOIN', array($channels, $keys));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ircPart().
     *
     * @param string $channels
     * @param string|null $message
     */
    public function ircPart($channels, $message = null)
    {
        $this->queueIrcRequest('PART', array($channels, $message));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ircMode().
     *
     * @param string $target
     * @param string|null $mode
     * @param string|null $param
     */
    public function ircMode($target, $mode = null, $param = null)
    {
        $this->queueIrcRequest('MODE', array($target, $mode, $param));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ircTopic().
     *
     * @param string $channel
     * @param string $topic
     */
    public function ircTopic($channel, $topic = null)
    {
        $this->queueIrcRequest('TOPIC', array($channel, $topic));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ircNames().
     *
     * @param string $channels
     */
    public function ircNames($channels)
    {
        $this->queueIrcRequest('NAMES', array($channels));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ircList().
     *
     * @param string $channels
     * @param string $server
     */
    public function ircList($channels = null, $server = null)
    {
        $this->queueIrcRequest('LIST', array($channels, $server));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ircInvite().
     *
     * @param string $nickname
     * @param string $channel
     */
    public function ircInvite($nickname, $channel)
    {
        $this->queueIrcRequest('INVITE', array($nickname, $channel));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ircKick().
     *
     * @param string $channel
     * @param string $user
     * @param string $comment
     */
    public function ircKick($channel, $user, $comment = null)
    {
        $this->queueIrcRequest('KICK', array($channel, $user, $comment));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ircVersion().
     *
     * @param string $server
     */
    public function ircVersion($server = null)
    {
        $this->queueIrcRequest('VERSION', array($server));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ircStats().
     *
     * @param string $query
     * @param string $server
     */
    public function ircStats($query, $server = null)
    {
        $this->queueIrcRequest('STATS', array($query, $server));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ircLinks().
     *
     * @param string $servermask
     * @param string $remoteserver
     */
    public function ircLinks($servermask = null, $remoteserver = null)
    {
        $this->queueIrcRequest('LINKS', array($servermask, $remoteserver));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ircTime().
     *
     * @param string $server
     */
    public function ircTime($server = null)
    {
        $this->queueIrcRequest('TIME', array($server));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ircConnect().
     *
     * @param string $targetserver
     * @param int $port
     * @param string $remoteserver
     */
    public function ircConnect($targetserver, $port = null, $remoteserver = null)
    {
        $this->queueIrcRequest('CONNECT', array($targetserver, $port, $remoteserver));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ircTrace().
     *
     * @param string $server
     */
    public function ircTrace($server = null)
    {
        $this->queueIrcRequest('TRACE', array($server));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ircAdmin()
     *
     * @param string $server
     */
    public function ircAdmin($server = null)
    {
        $this->queueIrcRequest('ADMIN', array($server));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ircInfo().
     *
     * @param string $server
     */
    public function ircInfo($server = null)
    {
        $this->queueIrcRequest('INFO', array($server));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ircPrivmsg().
     *
     * @param string $receivers
     * @param string $text
     */
    public function ircPrivmsg($receivers, $text)
    {
        $this->queueIrcRequest('PRIVMSG', array($receivers, $text));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ircNotice().
     *
     * @param string $nickname
     * @param string $text
     */
    public function ircNotice($nickname, $text)
    {
        $this->queueIrcRequest('NOTICE', array($nickname, $text));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ircWho().
     *
     * @param string $name
     * @param string $o
     */
    public function ircWho($name, $o = null)
    {
        $this->queueIrcRequest('WHO', array($name, $o));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ircWhois().
     *
     * @param string $nickmasks
     * @param string $server Optional
     */
    public function ircWhois($nickmasks, $server = null)
    {
        $this->queueIrcRequest('WHOIS', array($server, $nickmasks));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ircWhowas().
     *
     * @param string $nickname
     * @param int $count
     * @param string $server
     */
    public function ircWhowas($nickname, $count = null, $server = null)
    {
        $this->queueIrcRequest('WHOWAS', array($nickname, $count, $server));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ircKill().
     *
     * @param string $nickname
     * @param string $comment
     */
    public function ircKill($nickname, $comment)
    {
        $this->queueIrcRequest('KILL', array($nickname, $comment));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ircPing().
     *
     * @param string $server1
     * @param string $server2
     */
    public function ircPing($server1, $server2 = null)
    {
        $this->queueIrcRequest('PING', array($server1, $server2));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ircPong().
     *
     * @param string $daemon
     * @param string $daemon2
     */
    public function ircPong($daemon, $daemon2 = null)
    {
        $this->queueIrcRequest('PONG', array($daemon, $daemon2));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ircError().
     *
     * @param string $message
     */
    public function ircError($message)
    {
        $this->queueIrcRequest('ERROR', array($message));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ircAway().
     *
     * @param string $message
     */
    public function ircAway($message = null)
    {
        $this->queueIrcRequest('AWAY', array($message));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ircRehash().
     */
    public function ircRehash()
    {
        $this->queueIrcRequest('REHASH');
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ircRestart().
     */
    public function ircRestart()
    {
        $this->queueIrcRequest('RESTART');
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ircSummon().
     *
     * @param string $user
     * @param string $server
     */
    public function ircSummon($user, $server = null)
    {
        $this->queueIrcRequest('SUMMON', array($user, $server));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ircUsers().
     *
     * @param string $server
     */
    public function ircUsers($server = null)
    {
        $this->queueIrcRequest('USERS', array($server));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ircWallops().
     *
     * @param string $text
     */
    public function ircWallops($text)
    {
        $this->queueIrcRequest('WALLOPS', array($text));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ircUserhost().
     *
     * @param string $nickname1
     * @param string $nickname2
     * @param string $nickname3
     * @param string $nickname4
     * @param string $nickname5
     */
    public function ircUserhost($nickname1, $nickname2 = null, $nickname3 = null, $nickname4 = null, $nickname5 = null)
    {
        $this->queueIrcRequest('USERHOST', array($nickname1, $nickname2, $nickname3, $nickname4, $nickname5));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ircIson().
     *
     * @param string $nicknames
     */
    public function ircIson($nicknames)
    {
        $this->queueIrcRequest('ISON', array($nicknames));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ircProtoctl().
     *
     * @param string $proto
     */
    public function ircProtoctl($proto)
    {
        $this->queueIrcRequest('PROTOCTL', array($proto));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ctcpFinger().
     *
     * @param string $receivers
     */
    public function ctcpFinger($receivers)
    {
        $this->queueCtcpRequest('FINGER', array($receivers));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ctcpFingerResponse().
     *
     * @param string $nickname
     * @param string $text
     */
    public function ctcpFingerResponse($nickname, $text)
    {
        $this->queueCtcpResponse('FINGER', array($nickname, $text));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ctcpVersion().
     *
     * @param string $receivers
     */
    public function ctcpVersion($receivers)
    {
        $this->queueCtcpRequest('VERSION', array($receivers));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ctcpVersionResponse().
     *
     * @param string $nickname
     * @param string $name
     * @param string $version
     * @param string $environment
     */
    public function ctcpVersionResponse($nickname, $name, $version, $environment)
    {
        $this->queueCtcpResponse('VERSION', array($nickname, $name, $version, $environment));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ctcpSource().
     *
     * @param string $receivers
     */
    public function ctcpSource($receivers)
    {
        $this->queueCtcpRequest('SOURCE', array($receivers));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ctcpSourceResponse().
     *
     * @param string $nickname
     * @param string $host
     * @param string $directories
     * @param string $files
     */
    public function ctcpSourceResponse($nickname, $host, $directories, $files)
    {
        $this->queueCtcpResponse('SOURCE', array($nickname, $host, $directories, $files));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ctcpUserinfo().
     *
     * @param string $receivers
     */
    public function ctcpUserinfo($receivers)
    {
        $this->queueCtcpRequest('USERINFO', array($receivers));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ctcpUserinfoResponse().
     *
     * @param string $nickname
     * @param string $text
     */
    public function ctcpUserinfoResponse($nickname, $text)
    {
        $this->queueCtcpResponse('USERINFO', array($nickname, $text));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ctcpClientinfo().
     *
     * @param string $receivers
     */
    public function ctcpClientinfo($receivers)
    {
        $this->queueCtcpRequest('CLIENTINFO', array($receivers));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ctcpClientinfoResponse().
     *
     * @param string $nickname
     * @param string $client
     */
    public function ctcpClientinfoResponse($nickname, $client)
    {
        $this->queueCtcpResponse('CLIENTINFO', array($nickname, $client));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ctcpErrmsg().
     *
     * @param string $receivers
     * @param string $query
     */
    public function ctcpErrmsg($receivers, $query)
    {
        $this->queueCtcpRequest('ERRMSG', array($receivers, $query));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ctcpErrmsgResponse().
     *
     * @param string $nickname
     * @param string $query
     * @param string $message
     */
    public function ctcpErrmsgResponse($nickname, $query, $message)
    {
        $this->queueCtcpResponse('ERRMSG', array($nickname, $query, $message));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ctcpPing().
     *
     * @param string $receivers
     * @param int $timestamp
     */
    public function ctcpPing($receivers, $timestamp)
    {
        $this->queueCtcpRequest('PING', array($receivers, $timestamp));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ctcpPingResponse().
     *
     * @param string $nickname
     * @param int $timestamp
     */
    public function ctcpPingResponse($nickname, $timestamp)
    {
        $this->queueCtcpResponse('PING', array($nickname, $timestamp));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ctcpTime().
     *
     * @param string $receivers
     */
    public function ctcpTime($receivers)
    {
        $this->queueCtcpRequest('TIME', array($receivers));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ctcpTimeResponse().
     *
     * @param string $nickname
     * @param string $time
     */
    public function ctcpTimeResponse($nickname, $time)
    {
        $this->queueCtcpResponse('TIME', array($nickname, $time));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ctcpAction().
     *
     * @param string $receivers
     * @param string $action
     */
    public function ctcpAction($receivers, $action)
    {
        $this->queueCtcpRequest('ACTION', array($receivers, $action));
    }

    /**
     * Implements \Phergie\Irc\GeneratorInterface->ctcpActionResponse().
     *
     * @param string $nickname
     * @param string $action
     */
    public function ctcpActionResponse($nickname, $action)
    {
        $this->queueCtcpResponse('ACTION', array($nickname, $action));
    }

    /**
     * Returns a list of IRC events in order from most to least destructive.
     *
     * @return array Associative array keyed by event name
     */
    protected function getPriorities()
    {
        return array_flip(array(
            'RESTART',
            'SQUIT',
            'QUIT',
            'ERROR',
            'KICK',
            'PART',
            'KILL',
            'INVITE',
            'TOPIC',
            'ACTION',
            'PRIVMSG',
            'NICK',
            'MODE',
            'WHOWAS',
            'WHOIS',
            'WHO',
            'INFO',
            'ADMIN',
            'TRACE',
            'TIME',
            'LINKS',
            'STATS',
            'VERSION',
            'NAMES',
            'LIST',
            'JOIN',
            'NOTICE',
            'PONG',
            'PING',
            'USER',
            'PASS',
            'ISON',
            'USERHOST',
            'WALLOPS',
            'PROTOCTL',
            'USERS',
            'SUMMON',
            'REHASH',
            'AWAY',
            'CONNECT',
            'OPER',
            'SERVER',
        ));
    }
}
