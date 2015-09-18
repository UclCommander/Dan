<?php

/**
 * Cyanide command. Fetches a random (or by id) comic from explosm.
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */

use Dan\Helpers\Web;
use Illuminate\Support\Collection;

hook('cyanide')
    ->command(['cyanide', 'cy', 'ch'])
    ->func(function(Collection $args) {
        $id = intval($args->get('message'));

        if(!$id)
            $id = 'random';

        $url = get_final_url('http://explosm.net/comics/' . $id);

        $comic  = Web::dom($url);
        $image  = $comic->getElementById('main-comic');
        $src    = "http:" . $image->getAttribute('src');

        $id = last(array_filter(explode('/', $url)));

        $args->get('channel')->message("[ <yellow>#{$id}</yellow> | <cyan>{$src}</cyan> ]");
    });