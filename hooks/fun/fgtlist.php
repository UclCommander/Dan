<?php

/**
 * Fgtlist command. It just exists.
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */

use Illuminate\Support\Collection;

hook('fgtlist')
    ->command(['fgtlist', 'fgts'])
    ->help('Gets da fgts')
    ->func(function(Collection $args) {
        $list = [
            'Chris',
            'Mirz <3',
            'RoboDash',
        ];

        foreach($list as $fgt)
            $args->get('user')->notice($fgt);
    });