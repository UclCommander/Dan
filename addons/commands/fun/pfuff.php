<?php

/**
 * @author matthijs186
 *
 * PINK FLUFFY UNICORNS DANCING ON RAINBOWS
 * pomf pomf pomf
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

command(['pfuff', 'pfudor'])
    ->allowPrivate()
    ->helpText('Pink fluffy unicorns!')
    ->handler(function (User $user, Channel $channel = null) {
        $location = $channel ?? $user;

        $location->message('<pink>PINK FLUFFY UNICORNS DANCING ON RAINBOWS</pink> https://youtu.be/qRC4Vk6kisY');
    });
