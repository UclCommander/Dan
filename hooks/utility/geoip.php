<?php

use Illuminate\Support\Collection;

hook('geoip')
    ->command(['geoip'])
    ->help("Gets geographic information for the given IP.")
    ->func(function(Collection $args) {
        $message = $args->get('message');
        $channel = $args->get('channel');

        if (empty($message)) {
            $channel->message("I need an IP to get information for!");
            return;
        }
        
        $data = \Dan\Helpers\Web::json("http://geoip.cf/api/{$message}");
        
        if ($data == null) {
            $channel->message("Unable to fetch information");
            return;
        }
        
        if (isset($data['success']) && !$data['success']) {
            $channel->message($data['message']);

            return;
        }
        
        $data = [
            "<cyan>Country:</cyan> <yellow>{$data['country']}</yellow>",
            "<cyan>Continent:</cyan> <yellow>{$data['continent']}</yellow>",
            "<cyan>Lat:</cyan> <yellow>{$data['latitude']}</yellow>",
            "<cyan>Lng:</cyan> <yellow>{$data['longitude']}</yellow>",
            "<cyan>Accuracy:</cyan> <yellow>{$data['accuracy']}</yellow>",
        ];
        
        $info = implode(" | ", $data);
        $channel->message("[ {$info} ]");

    });
