<?php

use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;
use Dan\Helpers\Web;

/** @var User $user */
/** @var Channel $channel */
/** @var string $message */
/** @var string $entry */

if($entry == 'use')
{
    $data = explode(' ', $message);

    if($data[0] == 'save')
    {
        database()->update('users', ['nick' => $user->nick()], ['info' => ['lastfm' => $data[1]]]);
        message($channel, "{reset}[ {yellow}{$data[1]} {reset}is now saved to your nickname. ]");

    }
    else
    {
        $fmUser = empty($message) ? null : $data[0];

        if($fmUser == null)
        {
            $fmUser = $user->nick();

            $data = database()->get('users', ['nick' => $user->nick()]);

            if(!isset($data['info']))
                return;

            if(isset($data['info']['lastfm']))
                $fmUser = $data['info']['lastfm'];
        }

        $web = Web::rss("http://ws.audioscrobbler.com/1.0/user/{$fmUser}/recenttracks.rss");

        $title = explode("'", $web['title']);
        $title = reset($title);

        preg_match_all('/((?:^|[A-Z])[a-z]+)/', $title, $matches);

        $title = implode(' ', $matches[0]);

        if(!isset($web['item']))
        {
            message($channel, "{reset}[ Nothing found for {yellow}{$title} {reset}]");
            return;
        }

        message($channel, "{reset}[ {yellow}{$title}{reset} is listening to{cyan} {$web['item'][0]['title']} {reset}]");
    }
}


if($entry == 'help')
{
    return [
        "{CP}lastfm - Gets the last song for your saved nickname",
        "{CP}lastfm <user> - Gets the last song for the given user.",
        "{CP}lastfm save <user> - Saves a LastFM user for your current nickname",
    ];
}