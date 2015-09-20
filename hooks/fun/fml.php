<?php

/**
 * FML Command. Gets a random F My Life.
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */

use Dan\Helpers\Web;
use Illuminate\Support\Collection;

hook('fmylife')
    ->command(['fmylife', 'fml'])
    ->help('Gets a random F My Life.')
    ->func(function(Collection $args) {
        $data = Web::api("fml/random");

        if(empty($data))
        {
            $args->get('channel')->message("[ <yellow>#21</yellow> | <cyan>Error fetching random FML. FML </cyan>| <green>+9001</green>/<red>-420</red> ]");
            return;
        }

        $args->get('channel')->message("[ <yellow>{$data['id']}</yellow> | <cyan>{$data['text']}</cyan> | <green>+{$data['sucks']}</green>/<red>-{$data['deserved']}</red> ]");

    });
