<?php

/**
 * ヽ༼ຈل͜ຈ༽ﾉ
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */

use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;


command('dongers')
    ->usableInPrivate()
    ->helpText('RAISE THE DONGERS')
    ->handler(function (User $user, Channel $channel) {
        $location = $channel ?? $user;
        $location->message('ヽ༼ຈل͜ຈ༽ﾉ raise your dongers ヽ༼ຈل͜ຈ༽ﾉ');
    });