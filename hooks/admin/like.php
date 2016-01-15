<?php


use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;
use Illuminate\Support\Collection;

hook('like')
    ->command(['clones', 'like'])
    ->rank('vhoaqAS')
    ->help([
        "Searches users for same or similar hosts",
    ])
    ->func(function(Collection $args) {
        $host       = $args->get('message');
        /** @var Channel $channel */
        $channel    = $args->get('channel');
        /** @var User $user */
        $user    = $args->get('user');

        /** @var array $users */
        $users = $channel->getUsers();

        $matches = [];

        if($channel->hasUser($host)) {
            $host = "*" . $channel->getUser($host)->host() . "*";
        }

        foreach ($users as $nick => $cuser) {
            $cuser = user($nick);

            if(fnmatch($host, $cuser->host()))
                $matches[] = "{$cuser->nick()} [{$cuser->host()}]";
        }

        if(count($matches) == 0) {
            $user->notice("No matches found!");
        }

        if(count($matches) > 8){
            $count = count($matches);
            $matches = array_splice($matches, 0, 7);
            $matches[] = "and " . ($count - 8) . ' more...';
        }

        foreach($matches as $match) {
            $user->notice($match);
        }
    });
