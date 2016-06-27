<?php

use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;
use Dan\Services\Title\SteamTitleFetcher;

command(['rsg'])
    ->allowPrivate()
    ->helpText([
        'Gets a random steam game',
    ])
    ->handler(function (User $user, SteamTitleFetcher $fetcher, Channel $channel = null) {
        $location = $channel ?? $user;

        try {
            $items = $fetcher->fetchRandomGame();
        } catch (Exception $e) {
            console()->exception($e);
            
            $location->message($e->getMessage());

            return;
        }

        $location->message('[ Steam ] '.implode(' - ', array_filter($items)), [
            'lord_gaben_has_spoken' => ['white', 'green'],
        ]);
    });
