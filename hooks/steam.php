<?php

use Dan\Helpers\Web;

$regex = "#\b(?:.*)http\:\/\/fm1337\.com\/steam\/([a-zA-Z0-9]+)(?:.*)#";

hook(['regex' => $regex, 'name' => 'steam'], function(array $eventData, array $matches) {

    $items = [];

    foreach($matches[1] as $match)
    {
        $data = Web::json("http://fm1337.com/api/steam/{$match}");

        if($data == null)
        {
            $items[] = "{reset}[ {cyan}Error fetching steam information. {reset}]";
            continue;
        }

        $games          = $data['games']['game_count'];
        $realname       = isset($data['current']['realname']) ? $data['current']['realname'] : $data['current']['personaname'];
        $level          = $data['level'];
        $played         = $data['games']['total_played'];
        $playtime       = $data['games']['total_playtime'];
        $currentGame    = isset($data['current']['gameextrainfo']) ? $data['current']['gameextrainfo'] : null;
        $online         = $data['current']['personastate'] > 0 ? "{green}ONLINE" : "{red}OFFLINE";

        if($currentGame)
            $currentGame = " {cyan}Currently playing {yellow}{$currentGame} {reset}|";

        $items[] = "{reset}[ {$online} {reset}| {yellow}{$realname} {reset}| {light_cyan}Level {$level} {reset}|{$currentGame} {light_cyan}{$playtime} {cyan}hours on record {reset}| {light_cyan}{$played}{cyan} of {light_cyan}{$games} {cyan}games played {reset}]";

    }

    return $items;
});