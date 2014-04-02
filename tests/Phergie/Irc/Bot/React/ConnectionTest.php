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

use Phake;

/**
 * Tests for Connection.
 *
 * @category Phergie
 * @package Phergie\Irc\Bot\React
 */
class ConnectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Instantiates the class under test.
     */
    protected function setUp()
    {
        $this->connection = new Connection(array());
    }

    /**
     * Tests setting properties via the constructor.
     */
    public function testConstructorSetsProperties()
    {
        $config = array(
            'serverHostname' => 'serverHostname',
            'serverPort' => 6668,
            'password' => 'password',
            'nickname' => 'nickname',
            'username' => 'username',
            'hostname' => 'hostname',
            'servername' => 'servername',
            'realname' => 'realname',
            'options' => array('foo' => 'bar'),
        );
        $connection = new Connection($config);
        $this->assertSame($config['serverHostname'], $connection->getServerHostname());
        $this->assertSame($config['serverPort'], $connection->getServerPort());
        $this->assertSame($config['password'], $connection->getPassword());
        $this->assertSame($config['nickname'], $connection->getNickname());
        $this->assertSame($config['username'], $connection->getUsername());
        $this->assertSame($config['hostname'], $connection->getHostname());
        $this->assertSame($config['servername'], $connection->getServername());
        $this->assertSame($config['realname'], $connection->getRealname());
        $this->assertSame($config['options'], $connection->getOptions());
    }

    /**
     * Tests setPlugins().
     */
    public function testSetPlugins()
    {
        $plugins = array();
        foreach (range(1, 2) as $plugin) {
            $plugins[] = Phake::mock('\Phergie\Irc\Bot\React\PluginInterface');
        }
        $this->connection->setPlugins($plugins);
        $this->assertSame($plugins, $this->connection->getPlugins());
    }

    /**
     * Tests getPlugins().
     */
    public function testGetPlugins()
    {
        $this->assertEmpty($this->connection->getPlugins());
    }

    /**
     * Tests getOptions().
     */
    public function testGetOptions()
    {
        $this->assertEmpty($this->connection->getOptions());
    }

    /**
     * Tests setOptions().
     */
    public function testSetOptions()
    {
        $options = array(
            'foo' => 'bar',
            'baz' => 'bay',
        );
        $this->connection->setOptions($options);
        $this->assertSame($options, $this->connection->getOptions());
    }
}
