<?php
/**
 * Phergie (http://phergie.org)
 *
 * @link http://github.com/phergie/phergie-irc-bot-react for the canonical source repository
 * @copyright Copyright (c) 2008-2014 Phergie Development Team (http://phergie.org)
 * @license http://phergie.org/license Simplified BSD License
 * @package Phergie\Irc\Bot\React
 */

namespace Phergie\Irc\Tests\Bot\React;

use Phergie\Irc\Bot\React\EventQueue;

/**
 * Tests for EventQueue.
 *
 * @category Phergie
 * @package Phergie\Irc\Bot\React
 */
class EventQueueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Instance of the class under test
     *
     * @var \Phergie\Irc\Bot\React\EventQueue
     */
    protected $queue;

    /**
     * Instantiates the class under test.
     */
    protected function setUp()
    {
        $this->queue = new EventQueue;
    }

    /**
     * Tests the queue with IRC events.
     * $event_params is the same as $params if set to null.
     *
     * @param string $method
     * @param string $command
     * @param array $params
     * @param array|null $event_params
     * @param string|null $prefix
     * @dataProvider dataProviderIrcEvents
     */
    public function testIrcEvents($method, $command, array $params = array(), array $event_params = null, $prefix = null)
    {
        if ($event_params == null) {
            $event_params = $params;
        }
        $this->queue->setPrefix($prefix);
        call_user_func_array(array($this->queue, $method), $params);
        $event = $this->queue->extract();
        $this->assertInstanceOf('\Phergie\Irc\Event\UserEvent', $event);
        $this->assertSame($command, $event->getCommand());
        $this->assertSame($event_params, $event->getParams());
        $this->assertSame($prefix, $event->getPrefix());
    }

    /**
     * Data provider for testIrcEvents().
     *
     * @return array
     */
    public function dataProviderIrcEvents()
    {
        $data = array(
            array('ircPass', 'PASS', array('password')),
            array('ircNick', 'NICK', array('nickname', 'hopcount')),
            array('ircUser', 'USER', array('username', 'hostname', 'servername', 'realname')),
            array('ircServer', 'SERVER', array('servername', 'hopcount', 'info')),
            array('ircOper', 'OPER', array('user', 'password')),
            array('ircQuit', 'QUIT', array('message')),
            array('ircSquit', 'SQUIT', array('server', 'comment')),
            array('ircJoin', 'JOIN', array('channels', 'keys')),
            array('ircPart', 'PART', array('channels', 'message')),
            array('ircMode', 'MODE', array('target', 'mode', 'param')),
            array('ircTopic', 'TOPIC', array('channel', 'topic')),
            array('ircNames', 'NAMES', array('channels')),
            array('ircList', 'LIST', array('channels', 'server')),
            array('ircInvite', 'INVITE', array('nickname', 'channel')),
            array('ircKick', 'KICK', array('channel', 'user', 'comment')),
            array('ircVersion', 'VERSION', array('server')),
            array('ircStats', 'STATS', array('query', 'server')),
            array('ircLinks', 'LINKS', array('servermask', 'remoteserver')),
            array('ircTime', 'TIME', array('server')),
            array('ircConnect', 'CONNECT', array('targetserver', 'port', 'remoteserver')),
            array('ircTrace', 'TRACE', array('server')),
            array('ircAdmin', 'ADMIN', array('server')),
            array('ircInfo', 'INFO', array('server')),
            array('ircPrivmsg', 'PRIVMSG', array('receivers', 'text')),
            array('ircNotice', 'NOTICE', array('nickname', 'text')),
            array('ircWho', 'WHO', array('name', 'o')),
            array('ircWhois', 'WHOIS', array('nickmasks', 'server'), array('server', 'nickmasks')),
            array('ircWhowas', 'WHOWAS', array('nickname', 'count', 'server')),
            array('ircKill', 'KILL', array('nickname', 'comment')),
            array('ircPing', 'PING', array('server1', 'server2')),
            array('ircPong', 'PONG', array('daemon', 'daemon2')),
            array('ircError', 'ERROR', array('message')),
            array('ircAway', 'AWAY', array('message')),
            array('ircRehash', 'REHASH'),
            array('ircRestart', 'RESTART'),
            array('ircSummon', 'SUMMON', array('user', 'server')),
            array('ircUsers', 'USERS', array('server')),
            array('ircWallops', 'WALLOPS', array('text')),
            array('ircUserhost', 'USERHOST', array('nickname1', 'nickname2', 'nickname3', 'nickname4', 'nickname5')),
            array('ircIson', 'ISON', array('nicknames')),
            array('ircProtoctl', 'PROTOCTL', array('proto')),
        );

        foreach ($data as $value) {
            if (count($value) == 2) {
                $value[] = array();
                $value[] = null;
            } elseif (count($value) == 3) {
                $value[] = null;
            }
            $value[] = 'prefix';
            $data[] = $value;
        }

        return $data;
    }

    /**
     * Tests the queue with CTCP events.
     *
     * @param string $method
     * @param string $command
     * @param string $ctcpCommand
     * @param array $params
     * @param string|null $prefix
     * @dataProvider dataProviderCtcpEvents
     */
    public function testCtcpEvents($method, $command, $ctcpCommand, array $params = array(), $prefix = null)
    {
        call_user_func_array(array($this->queue, $method), $params);
        $event = $this->queue->extract();
        $this->assertInstanceOf('\Phergie\Irc\Event\CtcpEvent', $event);
        $this->assertSame($command, $event->getCommand());
        $this->assertSame($ctcpCommand, $event->getCtcpCommand());
        $this->assertSame($params, $event->getParams());
        $this->assertNull($event->getPrefix());
    }

    /**
     * Data provider for testCtcpEvents().
     *
     * @return array
     */
    public function dataProviderCtcpEvents()
    {
        $data = array(
            array('ctcpFinger', 'PRIVMSG', 'FINGER', array('receivers')),
            array('ctcpFingerResponse', 'NOTICE', 'FINGER', array('nickname', 'text')),
            array('ctcpVersion', 'PRIVMSG', 'VERSION', array('receivers')),
            array('ctcpVersionResponse', 'NOTICE', 'VERSION', array('nickname', 'name', 'version', 'environment')),
            array('ctcpSource', 'PRIVMSG', 'SOURCE', array('receivers')),
            array('ctcpSourceResponse', 'NOTICE', 'SOURCE', array('nickname', 'host', 'directories', 'files')),
            array('ctcpUserinfo', 'PRIVMSG', 'USERINFO', array('receivers')),
            array('ctcpUserinfoResponse', 'NOTICE', 'USERINFO', array('nickname', 'text')),
            array('ctcpClientinfo', 'PRIVMSG', 'CLIENTINFO', array('receivers')),
            array('ctcpClientinfoResponse', 'NOTICE', 'CLIENTINFO', array('nickname', 'client')),
            array('ctcpErrmsg', 'PRIVMSG', 'ERRMSG', array('receivers', 'query')),
            array('ctcpErrmsgResponse', 'NOTICE', 'ERRMSG', array('nickname', 'query', 'message')),
            array('ctcpPing', 'PRIVMSG', 'PING', array('receivers', 'timestamp')),
            array('ctcpPingResponse', 'NOTICE', 'PING', array('nickname', 'timestamp')),
            array('ctcpTime', 'PRIVMSG', 'TIME', array('receivers')),
            array('ctcpTimeResponse', 'NOTICE', 'TIME', array('nickname', 'time')),
            array('ctcpAction', 'PRIVMSG', 'ACTION', array('receivers', 'action')),
            array('ctcpActionResponse', 'NOTICE', 'ACTION', array('nickname', 'action')),
        );

        foreach ($data as $value) {
            $value[] = 'prefix';
            $data[] = $value;
        }

        return $data;
    }

    /**
     * Tests extract().
     */
    public function testExtract()
    {
        $this->assertNull($this->queue->extract());
        $this->queue->ircPrivmsg('#channel', 'text');
        $this->assertInstanceOf('\Phergie\Irc\Event\EventInterface', $this->queue->extract());
        $this->assertNull($this->queue->extract());
    }

    /**
     * Tests ordering by command priority then FIFO.
     */
    public function testPriorities()
    {
        // start with empty queue
        $this->assertNull($this->queue->extract());

        // queue a bunch of stuff
        $this->queue->ircQuit('Bye!');
        $this->queue->ircPrivmsg('#channel', 'text1');
        $this->queue->ircPrivmsg('#channel', 'text2');
        $this->queue->ircPrivmsg('#channel', 'text3');

        // verify order of output
        $event = $this->queue->extract();
        $this->assertInstanceOf('\Phergie\Irc\Event\EventInterface', $event);
        $this->assertEquals('PRIVMSG', $event->getCommand());
        $this->assertEquals(['#channel', 'text1'], $event->getParams());

        $event = $this->queue->extract();
        $this->assertInstanceOf('\Phergie\Irc\Event\EventInterface', $event);
        $this->assertEquals('PRIVMSG', $event->getCommand());
        $this->assertEquals(['#channel', 'text2'], $event->getParams());

        $event = $this->queue->extract();
        $this->assertInstanceOf('\Phergie\Irc\Event\EventInterface', $event);
        $this->assertEquals('PRIVMSG', $event->getCommand());
        $this->assertEquals(['#channel', 'text3'], $event->getParams());

        $event = $this->queue->extract();
        $this->assertInstanceOf('\Phergie\Irc\Event\EventInterface', $event);
        $this->assertEquals('QUIT', $event->getCommand());
        $this->assertEquals(['Bye!'], $event->getParams());

        $this->assertNull($this->queue->extract());
    }

    /**
     * Tests that iterating over the event queue does not truncate its contents.
     */
    public function testNonDestructiveIteration()
    {
        $this->assertNull($this->queue->extract());

        $this->queue->ircQuit();
        $contents = [];
        foreach ($this->queue as $value) {
            $contents[] = $value;
        }

        $event = $this->queue->extract();
        $this->assertInstanceOf('\Phergie\Irc\Event\EventInterface', $event);
        $this->assertEquals('QUIT', $event->getCommand());
        $this->assertEmpty($event->getParams());

        $this->assertEquals([$event], $contents);
    }
}
