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
    public function testIrcEvents($method, $command, array $params = [], array $event_params = null, $prefix = null)
    {
        if ($event_params == null) {
            $event_params = $params;
        }
        $this->queue->setPrefix($prefix);
        call_user_func_array([ $this->queue, $method ], $params);
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
        $data = [
            [ 'ircPass', 'PASS', [ 'password' ] ],
            [ 'ircNick', 'NICK', [ 'nickname', 'hopcount' ] ],
            [ 'ircUser', 'USER', [ 'username', 'hostname', 'servername', 'realname' ] ],
            [ 'ircServer', 'SERVER', [ 'servername', 'hopcount', 'info' ] ],
            [ 'ircOper', 'OPER', [ 'user', 'password' ] ],
            [ 'ircQuit', 'QUIT', [ 'message' ] ],
            [ 'ircSquit', 'SQUIT', [ 'server', 'comment' ] ],
            [ 'ircJoin', 'JOIN', [ 'channels', 'keys' ] ],
            [ 'ircPart', 'PART', [ 'channels', 'message' ] ],
            [ 'ircMode', 'MODE', [ 'target', 'mode', 'param' ] ],
            [ 'ircTopic', 'TOPIC', [ 'channel', 'topic' ] ],
            [ 'ircNames', 'NAMES', [ 'channels' ] ],
            [ 'ircList', 'LIST', [ 'channels', 'server' ] ],
            [ 'ircInvite', 'INVITE', [ 'nickname', 'channel' ] ],
            [ 'ircKick', 'KICK', [ 'channel', 'user', 'comment' ] ],
            [ 'ircVersion', 'VERSION', [ 'server' ] ],
            [ 'ircStats', 'STATS', [ 'query', 'server' ] ],
            [ 'ircLinks', 'LINKS', [ 'servermask', 'remoteserver' ] ],
            [ 'ircTime', 'TIME', [ 'server' ] ],
            [ 'ircConnect', 'CONNECT', [ 'targetserver', 'port', 'remoteserver' ] ],
            [ 'ircTrace', 'TRACE', [ 'server' ] ],
            [ 'ircAdmin', 'ADMIN', [ 'server' ] ],
            [ 'ircInfo', 'INFO', [ 'server' ] ],
            [ 'ircPrivmsg', 'PRIVMSG', [ 'receivers', 'text' ] ],
            [ 'ircNotice', 'NOTICE', [ 'nickname', 'text' ] ],
            [ 'ircWho', 'WHO', [ 'name', 'o' ] ],
            [ 'ircWhois', 'WHOIS', [ 'nickmasks', 'server' ], [ 'server', 'nickmasks' ] ],
            [ 'ircWhowas', 'WHOWAS', [ 'nickname', 'count', 'server' ] ],
            [ 'ircKill', 'KILL', [ 'nickname', 'comment' ] ],
            [ 'ircPing', 'PING', [ 'server1', 'server2' ] ],
            [ 'ircPong', 'PONG', [ 'daemon', 'daemon2' ] ],
            [ 'ircError', 'ERROR', [ 'message' ] ],
            [ 'ircAway', 'AWAY', [ 'message' ] ],
            [ 'ircRehash', 'REHASH' ],
            [ 'ircRestart', 'RESTART' ],
            [ 'ircSummon', 'SUMMON', [ 'user', 'server' ] ],
            [ 'ircUsers', 'USERS', [ 'server' ] ],
            [ 'ircWallops', 'WALLOPS', [ 'text' ] ],
            [ 'ircUserhost', 'USERHOST', [ 'nickname1', 'nickname2', 'nickname3', 'nickname4', 'nickname5' ] ],
            [ 'ircIson', 'ISON', [ 'nicknames' ] ],
            [ 'ircProtoctl', 'PROTOCTL', [ 'proto' ] ],
        ];

        foreach ($data as $value) {
            if (count($value) == 2) {
                $value[] = [];
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
    public function testCtcpEvents($method, $command, $ctcpCommand, array $params = [], $prefix = null)
    {
        call_user_func_array([ $this->queue, $method ], $params);
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
        $data = [
            [ 'ctcpFinger', 'PRIVMSG', 'FINGER', [ 'receivers' ] ],
            [ 'ctcpFingerResponse', 'NOTICE', 'FINGER', [ 'nickname', 'text' ] ],
            [ 'ctcpVersion', 'PRIVMSG', 'VERSION', [ 'receivers' ] ],
            [ 'ctcpVersionResponse', 'NOTICE', 'VERSION', [ 'nickname', 'name', 'version', 'environment' ] ],
            [ 'ctcpSource', 'PRIVMSG', 'SOURCE', [ 'receivers' ] ],
            [ 'ctcpSourceResponse', 'NOTICE', 'SOURCE', [ 'nickname', 'host', 'directories', 'files' ] ],
            [ 'ctcpUserinfo', 'PRIVMSG', 'USERINFO', [ 'receivers' ] ],
            [ 'ctcpUserinfoResponse', 'NOTICE', 'USERINFO', [ 'nickname', 'text' ] ],
            [ 'ctcpClientinfo', 'PRIVMSG', 'CLIENTINFO', [ 'receivers' ] ],
            [ 'ctcpClientinfoResponse', 'NOTICE', 'CLIENTINFO', [ 'nickname', 'client' ] ],
            [ 'ctcpErrmsg', 'PRIVMSG', 'ERRMSG', [ 'receivers', 'query' ] ],
            [ 'ctcpErrmsgResponse', 'NOTICE', 'ERRMSG', [ 'nickname', 'query', 'message' ] ],
            [ 'ctcpPing', 'PRIVMSG', 'PING', [ 'receivers', 'timestamp' ] ],
            [ 'ctcpPingResponse', 'NOTICE', 'PING', [ 'nickname', 'timestamp' ] ],
            [ 'ctcpTime', 'PRIVMSG', 'TIME', [ 'receivers' ] ],
            [ 'ctcpTimeResponse', 'NOTICE', 'TIME', [ 'nickname', 'time' ] ],
            [ 'ctcpAction', 'PRIVMSG', 'ACTION', [ 'receivers', 'action' ] ],
            [ 'ctcpActionResponse', 'NOTICE', 'ACTION', [ 'nickname', 'action' ] ],
        ];

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
        $this->assertEquals([ '#channel', 'text1' ], $event->getParams());

        $event = $this->queue->extract();
        $this->assertInstanceOf('\Phergie\Irc\Event\EventInterface', $event);
        $this->assertEquals('PRIVMSG', $event->getCommand());
        $this->assertEquals([ '#channel', 'text2' ], $event->getParams());

        $event = $this->queue->extract();
        $this->assertInstanceOf('\Phergie\Irc\Event\EventInterface', $event);
        $this->assertEquals('PRIVMSG', $event->getCommand());
        $this->assertEquals([ '#channel', 'text3' ], $event->getParams());

        $event = $this->queue->extract();
        $this->assertInstanceOf('\Phergie\Irc\Event\EventInterface', $event);
        $this->assertEquals('QUIT', $event->getCommand());
        $this->assertEquals([ 'Bye!' ], $event->getParams());

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

        $this->assertEquals([ $event ], $contents);
    }
}
