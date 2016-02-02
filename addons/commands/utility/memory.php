<?php

use Dan\Irc\Connection;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

command(['memory'])
     ->usableInConsole()
     ->usableInPrivate()
     ->helpText("Gets the bot's memory usage")
     ->rank('ASC')
     ->handler(function (Connection $connection, User $user, Channel $channel = null) {
         $memory = convert(memory_get_usage());
         $peak = convert(memory_get_peak_usage());
         $connection->message($channel ?? $user, "[ <cyan>Memory Usage:</cyan> <yellow>{$memory}</yellow> | <cyan>Peak Usage:</cyan> <yellow>{$peak}</yellow> ]");

         return;
     });
