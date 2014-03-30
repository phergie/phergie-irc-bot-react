<?php

use Phergie\Irc\Bot\React\Connection;

return array(

    // Plugins to include for all connections, where any connection-specific
    // plugin configuration using the same plugin key will take precedence over
    // this configuration. The plugin key is an arbitrary value used only for
    // uniquely identifying plugins for this purpose.

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

            // Optional connection-specific plugin configuration, which uses
            // the same array structure as the global plugin configuration
            // above and will override that configuration on a per-plugin basis
            // where the same plugin keys are used.

            'plugins' => array(

                // ...

            ),
        )),

    )

);
