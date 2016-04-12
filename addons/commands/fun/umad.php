<?php

use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

command(['umad'])
    ->allowPrivate()
    ->helpText('umad?')
    ->handler(function (User $user, $message, Channel $channel = null) {
        $location = $channel ?? $user;

        $location->message(($message ? $message.': ' : '').'https://skycld.co/PYOrHaMWiJ.gif');
    });
