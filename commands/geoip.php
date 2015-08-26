<?php

use Dan\Irc\Location\Location;
use Dan\Irc\Location\User;

/** @var User $user */
/** @var Location $location */
/** @var string $message */
/** @var string $entry */

if($entry == 'use')
{
    if(empty($message))
    {
        message($location, "{reset}[{maroon} I need an IP to get information for! {reset}]");
        return;
    }

    $data = \Dan\Helpers\Web::json("http://geoip.cf/api/{$message}");

    if($data == null)
    {
        message($location, "Unable to fetch information");
        return;
    }

    if(isset($data['success']) && !$data['success'])
    {
        message($location, $data['message']);
        return;
    }

    $data = [
        "Country: {yellow}{$data['country']}",
        "Continent: {yellow}{$data['continent']}",
        "Lat: {yellow}{$data['latitude']}",
        "Lng: {yellow}{$data['longitude']}",
        "Accuracy: {yellow}{$data['accuracy']}"
    ];

    $info = implode(" {reset}|{cyan} ", $data);

    message($location, "{reset}[{cyan} {$info} {reset}]");
}

if($entry == 'help')
{
    return [
        "Gets Geo IP information for the given IP."
    ];
}