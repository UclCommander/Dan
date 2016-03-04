<?php

/**
 * Urban command. Searches the only dictionary known to man.
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */
use Dan\Support\Web;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

command(['urban'])
    ->allowPrivate()
    ->helpText('urban <text> - Searches the only dictionary known to man.')
    ->handler(function (User $user, $message, Channel $channel = null){
        $location = $channel ?? $user;

        $index = 0;
        $data = explode(' ', $message);

        if (!$data[0]) {
            $location->message('Please specify term to search.');

            return;
        }

        if (count($data) > 1) {
            if (is_numeric(last($data))) {
                $index = abs(last($data) - 1);
                array_pop($data);
            }
        }

        $msg = urlencode(implode(' ', $data));

        $json = Web::json("http://api.urbandictionary.com/v0/define?term={$msg}");

        if ($json == null) {
            $location->message('<error>Error fetching definition</error>');

            return;
        }

        if ($json['result_type'] == 'no_results') {
            $location->message("No definition found for {$data[0]}");

            return;
        }

        $list = $json['list'];
        $listTotal = count($list);
        $item = ($index > $listTotal ? $list[0] : $list[$index]);

        $cleanDef = str_replace('  ', ' ', str_replace(["\n", "\r"], ' ', $item['definition']));

        $split = substr($cleanDef, 0, 350);

        if (strlen($cleanDef) > 350) {
            $split = $split.'...';
        }

        $listN = $index + 1;
        $location->message("[ <yellow>{$item['word']}</yellow> - {$listN}/{$listTotal} ] <cyan>{$split}</cyan> - <green>+{$item['thumbs_up']}</green>/<red>-{$item['thumbs_down']}</red>");

        if (strlen($cleanDef) > 350) {
            $location->message("Read more: ".shortLink($item['permalink']));
        }
    });
