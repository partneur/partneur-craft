<?php

/**
 * General Configuration
 *
 * All of your system's general configuration settings go in here.
 * You can see a list of the default settings in craft/app/etc/config/defaults/general.php
 */


return array(
    '*' => array(
        'useEmailAsUsername' => true,
        'autoLoginAfterAccountActivation' => true
    ),

    'test.partneur.com' => array(
        'devMode' => true,
    ),

    'partneur.com' => array(
        'devMode' => false,
    )
);
