<?php

use Dan\Contracts\UserContract;
use Dan\Irc\Location\Channel;
use Dan\Support\Web;

command(['geoip'])
    ->allowPrivate()
    ->allowConsole()
    ->helpText('Gets geographic information for the given IP.')
    ->handler(function (UserContract $user, $message, Channel $channel = null) {
        $location = $channel ?? $user;

        if (empty($message)) {
            $location->message('I need an IP to get information for!');

            return;
        }

        $data = Web::json("http://geoip.cf/api/{$message}");

        if ($data == null) {
            $location->message('Unable to fetch IP information.');

            return;
        }

        if (isset($data['success']) && !$data['success']) {
            $location->message($data['message']);

            return;
        }

        $data = [
            "<cyan>Hostname:</cyan> <yellow>{$data['hostname']}</yellow>",
            "<cyan>Country:</cyan> <yellow>{$data['country']}</yellow>",
            "<cyan>Continent:</cyan> <yellow>{$data['continent']}</yellow>",
            "<cyan>Lat:</cyan> <yellow>{$data['latitude']}</yellow>",
            "<cyan>Lng:</cyan> <yellow>{$data['longitude']}</yellow>",
            "<cyan>Accuracy:</cyan> <yellow>{$data['accuracy']}</yellow>",
        ];

        $info = implode(' | ', $data);
        $location->message("[ {$info} ]");
    });
