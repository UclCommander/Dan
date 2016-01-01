<?php

/**
 * Urban command. Searches the only dictionary known to man.
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */

use Dan\Helpers\Web;
use Dan\Irc\Location\Channel;
use Illuminate\Support\Collection;

hook('urban')
    ->command(['urban'])
    ->help('urban <text> - Searches the only dictionary known to man.')
    ->func(function(Collection $args) {
        /** @var Channel $channel */
        $channel = $args->get('channel');
        $message = $args->get('message');

        $index = 0;
        $data = explode(' ', $message);

        if (count($data) > 1) {
            if (is_numeric(last($data))) {
                $index = abs(last($data) - 1);
                array_pop($data);
            }
        }

        $msg = urlencode(implode(' ', $data));

        $json = Web::json("http://api.urbandictionary.com/v0/define?term={$msg}");

        if ($json == null) {
            $channel->message("Error fetching definition");

            return;
        }

        if ($json['result_type'] == 'no_results') {
            $channel->message(" [ <cyan>No definition found</cyan> ]");

            return;
        }

        $list = $json['list'];
        $item = ($index > count($list) ? $list[0] : $list[$index]);

        $cleanDef = str_replace('  ', ' ', str_replace(["\n", "\r"], ' ', $item['definition']));

        $split = substr($cleanDef, 0, 350);

        $channel->message("[ <yellow>{$item['word']}</yellow> | <cyan>{$split}</cyan> | <green>+{$item['thumbs_up']}</green>/<red>-{$item['thumbs_down']}</red> ]");

        if (strlen($cleanDef) > 350) {
            $channel->message(" [ <cyan>Read more: ".shortLink($item['permalink'])."</cyan> ]");
        }
    });

