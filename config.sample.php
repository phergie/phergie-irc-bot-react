<?php

return array(

    // Plugins to include for all connections, where any connection-specific
    // plugin configuration using the same plugin key will take precedence over
    // this configuration. The plugin key is an arbitrary value used only for
    // uniquely identifying plugins for this purpose.

    'plugins' => array(

        'plugin-key' => array(

            // ...

        ),

    ),

    'connections' => array(

        // One array for each connection here

        array(

            // Required settings

            'host' => 'irc.freenode.net',
            'port' => 6667,
            'username' => 'Elazar',
            'realname' => 'Matthew Turland',
            'nick' => 'Phergie3',

            // Optional settings

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
        ),

    )

);
