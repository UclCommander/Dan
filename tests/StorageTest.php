<?php

require('TestBase.php');

$storage = \Dan\Storage\Storage::load('users');

$storage->save([
    'nick'      => 'Cancer',
    'user'      => 'cancer',
    'host'      => 'cancer.org',
    'channels'  => ['#UclCommander'],
]);

$storage->save([
    'nick'      => 'UclCommander',
    'user'      => 'UclCommander',
    'host'      => 'uclcommander.net',
    'channels'  => ['#UclCommander'],
]);