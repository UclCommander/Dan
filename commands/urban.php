<?php

use Dan\Helpers\Web;
use Dan\Irc\Location\Location;
use Dan\Irc\Location\User;

/** @var User $user */
/** @var Location $location */
/** @var string $message */
/** @var string $entry */

if($entry == 'use')
{
    $index = 0;
    $data = explode(' ', $message);

    if(is_numeric(last($data)))
    {
        $index = abs(last($data) - 1);
        array_pop($data);
    }

    $msg = urlencode(implode(' ', $data));

    $json = Web::json("http://api.urbandictionary.com/v0/define?term={$msg}");

    if($json == null)
    {
        message($location, "Error fetching definition");
        return;
    }

    if($json['result_type'] == 'no_results')
    {
        message($location, "{reset}[ {cyan}No definition found {reset}]");
        return;
    }

    $list       = $json['list'];
    $item       = ($index > count($list) ?  $list[0] : $list[$index]);

    $cleanDef   = str_replace('  ', ' ', str_replace(["\n", "\r"], ' ', $item['definition']));

    $split = substr($cleanDef, 0, 350);

    message($location, "{reset}[ {yellow}{$item['word']} {reset}| {cyan} {$split} {reset}| {green}+{$item['thumbs_up']}{reset}/{red}-{$item['thumbs_down']} {reset}]");

    if(strlen($cleanDef) > 350)
        message($location, "{reset}[{cyan} Read more: " . $item['permalink'] . " {reset}]");
}

if($entry == 'help')
{
    return [
        "urban <text> - Searches the only dictionary known to man."
    ];
}