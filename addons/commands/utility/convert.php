<?php

use Dan\Irc\Location\Channel;
use Dan\Support\Web;

command(['convert'])
    ->helpText('Converts something to something else using DuckDuckGo')
    ->handler(function (Channel $channel, $message) {
        $query = urlencode("convert {$message}");
        $request = Web::json("http://api.duckduckgo.com/?q={$query}&format=json&pretty=1");

        if (empty($request) || !is_array($request['Answer'])) {
            $channel->message('No conversion results.');

            return;
        }

        $answerData = $request['Answer']['data'];
        $channel->message("[ <yellow>{$answerData['markup_input']} {$answerData['left_unit']}</yellow> is <cyan>{$answerData['raw_answer']} {$answerData['right_unit']}</cyan> ]");
    });
