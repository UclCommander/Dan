<?php

use Dan\Helpers\Web;
use Illuminate\Support\Collection;

hook('convert')
    ->command(['convert'])
    ->help("Converts something to something else using DuckDuckGo")
    ->func(function(Collection $args) {
        $message = $args->get('message');
        $channel = $args->get('channel');

        $query = urlencode("convert {$message}");
        $request = Web::json("http://api.duckduckgo.com/?q={$query}&format=json&pretty=1");

        if (empty($request) || !is_array($request['Answer']))  {
            $channel->message("No conversion results.");
            return;
        }
        
        $answerData = $request['Answer']['data'];

        $channel->message("[ <yellow>{$answerData['markup_input']} {$answerData['left_unit']}</yellow> is <cyan>{$answerData['raw_answer']} {$answerData['right_unit']}</cyan> ]");
    });
