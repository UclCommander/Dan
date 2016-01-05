<?php

use Dan\Core\Dan;
use Endroid\Twitter\Twitter;
use Illuminate\Support\Collection;

hook('twitter_tweets')
    ->http()
    ->get('/twitter/update')
    ->func(function(Collection $args) {
        $config = config('web.routes.twitter_tweets');

        $twitter = new Twitter($config['api_key'], $config['api_secret'], $config['access_token'], $config['access_secret']);

        $db = database()->table('cache')->where('key', 'twitter_posted_tweets');
        $cache = $db->first()->get('value');

        if($db->count() == 0) {
            $db->insert(['key' => 'twitter_posted_tweets', 'value' => []]);
            $cache = [];
        }

        foreach($config['users'] as $user) {
            $data = $twitter->getTimeline(['screen_name' => $user['name'], 'count' => 1]);

            $data = $data[0];

            if(in_array($data->id_str, $cache)) {
                continue;
            }

            if (!Dan::hasConnection($user['network'])) {
                continue;
            }

            $connection = connection($user['network']);

            if (!$connection->inChannel($user['channel'])) {
                continue;
            }

            $text = htmlspecialchars_decode(cleanString($data->text), ENT_QUOTES);

            $connection->getChannel($user['channel'])
                ->message("[ Twitter ] <cyan>@{$data->user->screen_name}:</cyan> {$text} - " . shortLink("https://twitter.com/{$data->user->screen_name}/status/{$data->id_str}"));

            $cache[] = $data->id_str;
        }

        $db->update(['value' => $cache]);
    });