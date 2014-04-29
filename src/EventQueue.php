<?php
/**
 * Phergie (http://phergie.org)
 * @copyright Copyright (c) 2008-2014 Phergie Development Team (http://phergie.org)
 * @license http://phergie.org/license New BSD License
 * @package Phergie\Irc\Bot\React
 */

namespace Phergie\Irc\Bot\React;

use Phergie\Irc\Event\CtcpEvent;
use Phergie\Irc\Event\UserEvent;

/**
 * Queue to contain commands issued by plugins to be sent to servers so as to
 * allow for manipulation of those commands by plugins prior to their
 * transmission.
 *
 * @category Phergie
 * @package Phergie\Irc\Bot\React
 */
class EventQueue extends \SplPriorityQueue implements EventQueueInterface
{
    /**
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
     * Initializes the list of event priorities.
     */
    public function __construct()
    {
        parent::__construct();

        $this->priorities = array_flip(array(
            'RESTART',
            'SQUIT',
            'QUIT',
            'ERROR',
            'KICK',
            'PART',
            'KILL',
            'INVITE',
            'TOPIC',
            'NICK',
            'ACTION',
            'PRIVMSG',
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
            'USERS',
            'SUMMON',
            'REHASH',
            'AWAY',
            'CONNECT',
            'OPER',
            'SERVER',
        ));
    }

    /**
     * Removes and returns an event from the front of the queue.
     *
     * @return \Phergie\Irc\Event\EventInterface|null Removed event or null if
     *         the queue is empty
     */
    public function extract()
    {
        if ($this->isEmpty()) {
            return null;
        }
        return parent::extract();
    }

    /**
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
     * @return int
     */
    protected function getPriority($command, array $params)
    {
        return $this->priorities[$command];
    }

    /**
     * Enqueues a new IRC event.
     *
     * @param string $command
     * @param array $params
     */
    protected function queueIrcRequest($command, array $params = array())
    {
        $event = new UserEvent;
        $event->setPrefix($this->prefix);
        $event->setCommand($command);
        $event->setParams(array_filter($params));
        $this->insert($event, $this->getPriority($command, $params));
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
        $event->setPrefix($this->prefix);
        $event->setCommand($command);
        $event->setParams(array_filter($params));
        $event->setCtcpCommand($ctcpCommand);
        $this->insert($event, $this->getPriority($command, $params));
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
     * @param string $password
     */
    public function ircPass($password)
    {
        $this->queueIrcRequest('PASS', array($password));
    }

    /**
     * @param string $nickname
     * @param int $hopcount
     */
    public function ircNick($nickname, $hopcount = null)
    {
        $this->queueIrcRequest('NICK', array($nickname, $hopcount));
    }

    /**
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
     * @param string $servername
     * @param int $hopcount
     * @param string $info
     */
    public function ircServer($servername, $hopcount, $info)
    {
        $this->queueIrcRequest('SERVER', array($servername, $hopcount, $info));
    }

    /**
     * @param string $user
     * @param string $password
     */
    public function ircOper($user, $password)
    {
        $this->queueIrcRequest('OPER', array($user, $password));
    }

    /**
     * @param string $message
     */
    public function ircQuit($message = null)
    {
        $this->queueIrcRequest('QUIT', array($message));
    }

    /**
     * @param string $server
     * @param string $comment
     */
    public function ircSquit($server, $comment)
    {
        $this->queueIrcRequest('SQUIT', array($server, $comment));
    }

    /**
     * @param string $channels
     * @param string $keys
     */
    public function ircJoin($channels, $keys = null)
    {
        $this->queueIrcRequest('JOIN', array($channels, $keys));
    }

    /**
     * @param string $channels
     */
    public function ircPart($channels)
    {
        $this->queueIrcRequest('PART', array($channels));
    }

    /**
     * @param string $target
     * @param string $mode
     * @param string $param
     */
    public function ircMode($target, $mode, $param = null)
    {
        $this->queueIrcRequest('MODE', array($target, $mode, $param));
    }

    /**
     * @param string $channel
     * @param string $topic
     */
    public function ircTopic($channel, $topic = null)
    {
        $this->queueIrcRequest('TOPIC', array($channel, $topic));
    }

    /**
     * @param string $channels
     */
    public function ircNames($channels)
    {
        $this->queueIrcRequest('NAMES', array($channels));
    }

    /**
     * @param string $channels
     * @param string $server
     */
    public function ircList($channels = null, $server = null)
    {
        $this->queueIrcRequest('LIST', array($channels, $server));
    }

    /**
     * @param string $nickname
     * @param string $channel
     */
    public function ircInvite($nickname, $channel)
    {
        $this->queueIrcRequest('INVITE', array($nickname, $channel));
    }

    /**
     * @param string $channel
     * @param string $user
     * @param string $comment
     */
    public function ircKick($channel, $user, $comment = null)
    {
        $this->queueIrcRequest('KICK', array($channel, $user, $comment));
    }

    /**
     * @param string $server
     */
    public function ircVersion($server = null)
    {
        $this->queueIrcRequest('VERSION', array($server));
    }

    /**
     * @param string $query
     * @param string $server
     */
    public function ircStats($query, $server = null)
    {
        $this->queueIrcRequest('STATS', array($query, $server));
    }

    /**
     * @param string $servermask
     * @param string $remoteserver
     */
    public function ircLinks($servermask = null, $remoteserver = null)
    {
        $this->queueIrcRequest('LINKS', array($servermask, $remoteserver));
    }

    /**
     * @param string $server
     */
    public function ircTime($server = null)
    {
        $this->queueIrcRequest('TIME', array($server));
    }

    /**
     * @param string $targetserver
     * @param int $port
     * @param string $remoteserver
     */
    public function ircConnect($targetserver, $port = null, $remoteserver = null)
    {
        $this->queueIrcRequest('CONNECT', array($targetserver, $port, $remoteserver));
    }

    /**
     * @param string $server
     */
    public function ircTrace($server = null)
    {
        $this->queueIrcRequest('TRACE', array($server));
    }

    /**
     * @param string $server
     */
    public function ircAdmin($server = null)
    {
        $this->queueIrcRequest('ADMIN', array($server));
    }

    /**
     * @param string $server
     */
    public function ircInfo($server = null)
    {
        $this->queueIrcRequest('INFO', array($server));
    }

    /**
     * @param string $receivers
     * @param string $text
     */
    public function ircPrivmsg($receivers, $text)
    {
        $this->queueIrcRequest('PRIVMSG', array($receivers, $text));
    }

    /**
     * @param string $nickname
     * @param string $text
     */
    public function ircNotice($nickname, $text)
    {
        $this->queueIrcRequest('NOTICE', array($nickname, $text));
    }

    /**
     * @param string $name
     * @param string $o
     */
    public function ircWho($name, $o = null)
    {
        $this->queueIrcRequest('WHO', array($name, $o));
    }

    /**
     * @param string $server
     * @param string $nickmasks
     */
    public function ircWhois($server, $nickmasks)
    {
        $this->queueIrcRequest('WHOIS', array($server, $nickmasks));
    }

    /**
     * @param string $nickname
     * @param int $count
     * @param string $server
     */
    public function ircWhowas($nickname, $count = null, $server = null)
    {
        $this->queueIrcRequest('WHOWAS', array($nickname, $count, $server));
    }

    /**
     * @param string $nickname
     * @param string $comment
     */
    public function ircKill($nickname, $comment)
    {
        $this->queueIrcRequest('KILL', array($nickname, $comment));
    }

    /**
     * @param string $server1
     * @param string $server2
     */
    public function ircPing($server1, $server2 = null)
    {
        $this->queueIrcRequest('PING', array($server1, $server2));
    }

    /**
     * @param string $daemon
     * @param string $daemon2
     */
    public function ircPong($daemon, $daemon2 = null)
    {
        $this->queueIrcRequest('PONG', array($daemon, $daemon2));
    }

    /**
     * @param string $message
     */
    public function ircError($message)
    {
        $this->queueIrcRequest('ERROR', array($message));
    }

    /**
     * @param string $message
     */
    public function ircAway($message = null)
    {
        $this->queueIrcRequest('AWAY', array($message));
    }

    public function ircRehash()
    {
        $this->queueIrcRequest('REHASH');
    }

    public function ircRestart()
    {
        $this->queueIrcRequest('RESTART');
    }

    /**
     * @param string $user
     * @param string $server
     */
    public function ircSummon($user, $server = null)
    {
        $this->queueIrcRequest('SUMMON', array($user, $server));
    }

    /**
     * @param string $server
     */
    public function ircUsers($server = null)
    {
        $this->queueIrcRequest('USERS', array($server));
    }

    /**
     * @param string $text
     */
    public function ircWallops($text)
    {
        $this->queueIrcRequest('WALLOPS', array($text));
    }

    /**
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
     * @param string $nicknames
     */
    public function ircIson($nicknames)
    {
        $this->queueIrcRequest('ISON', array($nicknames));
    }

    /**
     * @param string $receivers
     */
    public function ctcpFinger($receivers)
    {
        $this->queueCtcpRequest('FINGER', array($receivers));
    }

    /**
     * @param string $nickname
     * @param string $text
     */
    public function ctcpFingerResponse($nickname, $text)
    {
        $this->queueCtcpResponse('FINGER', array($nickname, $text));
    }

    /**
     * @param string $receivers
     */
    public function ctcpVersion($receivers)
    {
        $this->queueCtcpRequest('VERSION', array($receivers));
    }

    /**
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
     * @param string $receivers
     */
    public function ctcpSource($receivers)
    {
        $this->queueCtcpRequest('SOURCE', array($receivers));
    }

    /**
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
     * @param string $receivers
     */
    public function ctcpUserinfo($receivers)
    {
        $this->queueCtcpRequest('USERINFO', array($receivers));
    }

    /**
     * @param string $nickname
     * @param string $text
     */
    public function ctcpUserinfoResponse($nickname, $text)
    {
        $this->queueCtcpResponse('USERINFO', array($nickname, $text));
    }

    /**
     * @param string $receivers
     */
    public function ctcpClientinfo($receivers)
    {
        $this->queueCtcpRequest('CLIENTINFO', array($receivers));
    }

    /**
     * @param string $nickname
     * @param string $client
     */
    public function ctcpClientinfoResponse($nickname, $client)
    {
        $this->queueCtcpResponse('CLIENTINFO', array($nickname, $client));
    }

    /**
     * @param string $receivers
     * @param string $query
     */
    public function ctcpErrmsg($receivers, $query)
    {
        $this->queueCtcpRequest('ERRMSG', array($receivers, $query));
    }

    /**
     * @param string $nickname
     * @param string $query
     * @param string $message
     */
    public function ctcpErrmsgResponse($nickname, $query, $message)
    {
        $this->queueCtcpResponse('ERRMSG', array($nickname, $query, $message));
    }

    /**
     * @param string $receivers
     * @param int $timestamp
     */
    public function ctcpPing($receivers, $timestamp)
    {
        $this->queueCtcpRequest('PING', array($receivers, $timestamp));
    }

    /**
     * @param string $nickname
     * @param int $timestamp
     */
    public function ctcpPingResponse($nickname, $timestamp)
    {
        $this->queueCtcpResponse('PING', array($nickname, $timestamp));
    }

    /**
     * @param string $receivers
     */
    public function ctcpTime($receivers)
    {
        $this->queueCtcpRequest('TIME', array($receivers));
    }

    /**
     * @param string $nickname
     * @param string $time
     */
    public function ctcpTimeResponse($nickname, $time)
    {
        $this->queueCtcpResponse('TIME', array($nickname, $time));
    }

    /**
     * @para string $receivers
     * @param string $action
     */
    public function ctcpAction($receivers, $action)
    {
        $this->queueCtcpRequest('ACTION', array($receivers, $action));
    }

    /**
     * @param string $nickname
     * @param string $action
     */
    public function ctcpActionResponse($nickname, $action)
    {
        $this->queueCtcpResponse('ACTION', array($nickname, $action));
    }
}
