<?php

/**
 * Fgtlist command. It just exists.
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */

use Dan\Irc\Location\User;

command(['fgtlist', 'fgts'])
    ->allowPrivate()
    ->helpText('Get da fgts')
    ->handler(function (User $user) {
        $list = [
            'Chris',
            'Mirz <3',
            'RoboDash'
        ];

        foreach($list as $fgt) {
            $user->notice($fgt);
        }
    });