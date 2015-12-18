<?php

use Illuminate\Support\Collection;

hook('messenger')
    ->http()
    ->get('/messenger')
    ->func(function(Collection $args) {
        $data = $args->get('data') ?? $args->get('query');

        $connection = connection($data['server']);

        if(!$connection->inChannel($data['channel'])) {
            return response("I'm not in that channel", 500);
        }

        $connection->getChannel($data['channel'])
            ->message($data['message']);

        return response("Message sent");
    });