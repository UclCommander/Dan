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

    $url = get_final_url('https://' . ($id == 'random' ? 'c.' : '') . 'xkcd.com/' . $id . ($id == 'random' ? '/comic' : ''));

    $comic = Web::dom($url);

    $image  = $comic->getElementById('comic');

    if($image == null)
    {
        message($channel, "Error fetching comic");
        return;
    }

    $image = $image->getElementsByTagName('img');

    if(!$image->length)
    {
        message($channel, "Error fetching comic");
        return;
    }

    $image = $image->item(0);

    $src    = "http:" . $image->attributes->getNamedItem('src')->nodeValue;

    $content = $comic->getElementById('middleContainer')->textContent;

    preg_match("/http:\/\/xkcd\.com\/([0-9]+)\//i", $content, $matches);

    $id = $matches[1];

    message($channel, "{reset}[ {yellow}#{$id} {reset}|{cyan} {$src} {reset}]");
}

if($entry == 'help')
{
    return [
        "{CP}xkcd [id] - gets a comic by id (or a random one)"
    ];
}