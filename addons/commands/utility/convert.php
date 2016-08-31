<?php

use Dan\Irc\Location\Channel;
use Dan\Support\Web;

command(['convert'])
    ->helpText('Converts something to something else using DuckDuckGo')
    ->handler(function (Channel $channel, $message) {
        $query = urlencode("convert {$message}");
        $request = Web::json("http://api.duckduckgo.com/?q={$query}&format=json&pretty=1");

        if (empty($request) || empty($request['Answer'])) {
            $channel->message('No conversion results.');

            return;
        }

        $channel->message("[ <cyan>{$request['Answer']}</cyan> ]");
    });
