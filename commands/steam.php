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
    $data = explode(' ', $message);

    switch($data[0])
    {
        case 'save':
        {
            database()->table('users')->where('nick', $user->nick())->update(['info' => ['steam' => $data[1]]]);
            message($channel, "{reset}[ {yellow}{$data[1]} {cyan}is now saved to your nickname. ]");
            break;
        }

        default:
        {
            $person = empty($message) ? null : $data[0];

            if($person == null)
            {
                $person = $user->nick();

                $data = database()->table('users')->where('nick', $user->nick())->first();

                if(isset($data['info']))
                    if(isset($data['info']['steam']))
                        $person = $data['info']['steam'];
            }

            $data = Web::api('steam/get', ['user' => $person]);

            if($data == null)
            {
                message($channel, "{reset}[ {cyan}Error fetching information. {reset}]");
                return;
            }

            if($data['success'] == 42)
            {
                if($data['message'] == 'No Match')
                {
                    message($channel, "{reset}[ {cyan}This username has no match. Please use your steam username. {reset}]");
                    return;
                }

                message($channel, "{reset}[ {cyan}{$data['message']} {reset}]");
                return;
            }

            $games      = $data['games']['game_count'];
            $realname   = $data['current']['personaname'];
            $level      = $data['level'];
            $played     = $data['games']['total_played'];
            $playtime   = $data['games']['total_playtime'];
            $currentGame = isset($data['current']['gameextrainfo']) ? $data['current']['gameextrainfo'] : null;
            $online = $data['current']['personastate'] > 0 ? "{green}ONLINE" : "{red}OFFLINE";

            if($currentGame)
                $currentGame = " {cyan}Currently playing {yellow}{$currentGame} {reset}|";

            message($channel, "{reset}[ {$online} {reset}| {yellow}{$realname} {reset}| {light_cyan}Level {$level} {reset}|{$currentGame} {light_cyan}{$playtime} {cyan}hours on record {reset}| {light_cyan}{$played}{cyan} of {light_cyan}{$games} {cyan}games played {reset}]");
            break;
        }

    }
}

if($entry == 'help')
{
    return [
        "Gets steam information for the given user."
    ];
}