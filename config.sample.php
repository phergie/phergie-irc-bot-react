<?php

use Phergie\Irc\Bot\React\Connection;

return array(

    // Plugins to include for all connections

    'plugins' => array(

        // 'plugin-key' => new \Vendor\Plugin\PluginName(array(
        // /* configuration goes here */
        // )),

    ),

    'connections' => array(

        new Connection(array(

            // Required settings

            'host' => 'irc.freenode.net',
            'username' => 'Elazar',
            'realname' => 'Matthew Turland',
            'nick' => 'Phergie3',

            // Optional settings

            // 'servername' => 'user server name goes here if needed',
            // 'serverhost' => 'server host name goes here if needed',
            // 'port' => 6667,
            // 'password' => 'password goes here if needed',
            // 'options' => array(
            //     'transport' => 'ssl',
            //     'force-ipv4' => true,
            // )

            // Optional list of plugins to use specifically for this connection

            'plugins' => array(

                // 'plugin-key' => new \Vendor\Plugin\PluginName(array(
                // /* configuration goes here */
                // )),

            ),
        )),

    )

);
