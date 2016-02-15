<?php

/**
 * Let me google that for you. For _those_ people.
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */
use Dan\Irc\Location\Channel;

command(['lmgtfy', 'lazy'])
    ->helpText('For those lazy people.')
    ->handler(function (Channel $channel, $message) {

        if (!$message) {
            $channel->message('Can\'t get any lazier than that..');

            return;
        }

        $channel->message('http://lmgtfy.com/?q='.urlencode($message));
    });
