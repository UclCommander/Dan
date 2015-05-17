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
    $data = Web::api("fml/random");

    if(empty($data))
    {
        message($channel, "{reset}[ {yellow}#21 {reset}| {cyan}Error fetching random FML. FML {reset}| {green}+9001{reset}/{red}-420 {reset}]");
        return;
    }

    message($channel, "{reset}[ {yellow}{$data['id']} {reset}| {cyan}{$data['text']} {reset}| {green}+{$data['sucks']}{reset}/{red}-{$data['deserved']} {reset}]");
}

if($entry == 'help')
{
    return [
        "fml - gets a random fml"
    ];
}