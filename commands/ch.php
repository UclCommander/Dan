<?php

use Dan\Helpers\Web;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

/** @var User $user */
/** @var Channel $channel */
/** @var string $message */
/** @var string $entry */

if($entry == 'use')
{
    $id = intval($message);

    if(!$id)
        $id = 'random';

    $url = get_final_url('http://explosm.net/comics/' . $id);

    $comic = Web::dom($url);

    $image  = $comic->getElementById('main-comic');
    $src    = "http:" . $image->getAttribute('src');

    $id = last(array_filter(explode('/', $url)));

    message($channel, "{reset}[ {yellow}#{$id} {reset}|{cyan} {$src} {reset}]");
}

if($entry == 'help')
{
    return [
        "{CP}ch [id] - gets a comic by id (or a random one)"
    ];
}