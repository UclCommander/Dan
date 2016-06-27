<?php

use Dan\Irc\Location\Channel;
use Dan\Services\Title\SteamTitleFetcher;

on('irc.message.public')
    ->name('steam')
    ->match("/(?:.*)http\:\/\/store\.steampowered\.com\/app\/([0-9]+)?(?:.*)/")
    ->handler(function (Channel $channel, $matches, SteamTitleFetcher $fetcher) {
        try {
            $items = $fetcher->fetchTitle($matches[0][0]);
        } catch (Exception $e) {
            console()->exception($e);

            $channel->message($e->getMessage());

            return false;
        }

        $channel->message('[ Steam ] '.implode(' - ', array_filter($items)), [
            'lord_gaben_has_spoken' => ['white', 'green'],
        ]);

        return false;
    });
