<?php

/**
 * FML Command. Gets a random F My Life.
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */
use Dan\Irc\Location\Channel;
use Dan\Support\Web;

command(['fmylife', 'fml'])
    ->helpText('Gets a random F My Life.')
    ->handler(function (Channel $channel) {
        $data = Web::api('fml/random');

        if (empty($data)) {
            $channel->message('[ <yellow>#21</yellow> | <cyan>Error fetching random FML. FML </cyan>| <green>+9001</green>/<red>-420</red> ]');

            return;
        }

        $channel->message("[ <yellow>{$data['id']}</yellow> | <cyan>{$data['text']}</cyan> | <green>+{$data['sucks']}</green>/<red>-{$data['deserved']}</red> ]");
    });
