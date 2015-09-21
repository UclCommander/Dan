<?php

use Dan\Helpers\Web;
use Dan\Irc\Location\Channel;
use Illuminate\Support\Collection;

hook('speedtest')
    ->regex("/(?:.*)http\:\/\/www\.speedtest\.net\/(?:my\-)?result\/([0-9]+)(?:\.png)?(?:.*)/")
    ->func(function(Collection $args) {
        /** @var Channel $channel */
        $channel    = $args->get('channel');
        $matches    = $args->get('matches');

        $return = null;
        
        foreach ($matches[1] as $match)
        {
            $data = Web::api('speedtest/get', ['id' => $match]);

            $items = [
                "<cyan>Ping:</cyan> <light_cyan>{$data['ping']}</light_cyan>",
                "<cyan>Down:</cyan> <light_cyan>{$data['download']}</light_cyan>",
                "<cyan>Up:</cyan> <light_cyan>{$data['upload']}</light_cyan>",
                "<orange>ISP: {$data['isp']}</orange>",
                "<yellow>{$data['server']}</yellow>",
                "<green>{$data['stars']}</green>",
            ];

            $channel->message("[ " . implode(' | ', $items) . " ]");

            $return = false;
        }

        return $return;
    });