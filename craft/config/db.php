<?php

/**
 * Database Configuration
 *
 * All of your system's database configuration settings go in here.
 * You can see a list of the default settings in craft/app/etc/config/defaults/db.php
 * 
 * https://craftcms.com/docs/multi-environment-configs
 */


return array(
    '*' => array(
        'tablePrefix' => 'craft',
    ),
    '.dev' => array(
        'server' => 'localhost',
        'user' => 'root',
        'password' => 'vagrant',
        'database' => 'craft',
    ),
    'test.partneur.com' => array(
        'server' => 'localhost',
        'user' => 'partneur',
        'password' => 'Partneur360',
        'database' => 'partneur_test_craft',
    ),
    '.com' => array(
        'server' => 'localhost',
        'user' => 'partneur',
        'password' => 'Partneur360',
        'database' => 'partneur_craft',
    ),
    
);
