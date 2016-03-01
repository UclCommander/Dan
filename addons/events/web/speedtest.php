<?php

use Dan\Irc\Location\Channel;
use Dan\Support\Web;

on('irc.message.public')
    ->name('speedtest')
    ->match("/(?:.*)http\:\/\/www\.speedtest\.net\/(?:my\-)?result\/([0-9]+)(?:\.png)?(?:.*)/")
    ->handler(function (Channel $channel, $matches) {
        $return = null;

        foreach ($matches[1] as $match) {
            $data = Web::api('speedtest/get', ['id' => $match]);

            $items = [
                "<light_cyan>Ping: {$data['ping']}</light_cyan>",
                "<cyan>Download: {$data['download']}</cyan>",
                "<cyan>Upload: {$data['upload']}</cyan>",
                "<orange>ISP: {$data['isp']}</orange>",
                "<yellow>Server: {$data['server']}</yellow>",
                "<green>{$data['stars']}</green>",
            ];

            $channel->message('[ Speedtest ] '.implode(' - ', $items));
            $return = false;
        }

        return $return;
    });
