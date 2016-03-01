<?php

use Dan\Irc\Location\Channel;
use Dan\Support\Web;

on('irc.message.public')
    ->name('twitter_tweet')
    ->match("/https?:\/\/twitter\.com\/([a-zA-Z0-9_]+)\/status\/([0-9]{0,18})/")
    ->handler(function (Channel $channel, $matches) {
        $status = last(last($matches));

        if (!is_numeric($status)) {
            return true;
        }

        $data = Web::api('twitter/tweet', ['id' => $status]);
        $text = htmlspecialchars_decode(str_replace(["\n"], ' ', $data['text']));

        $data = [
            "<cyan>@{$data['user']['screen_name']}</cyan>",
            "<orange>{$data['favorite_count']}❤</orange>",
            "<orange>{$data['retweet_count']}RT</orange>",
            "<light_cyan>{$text}</light_cyan>",
        ];

        $channel->message('[ Twitter ] '.implode(' - ', $data));

        return false;
    });

on('irc.message.public')
    ->name('twitter_user')
    ->match("/https?:\/\/twitter\.com\/([a-zA-Z0-9_]+)\/?/")
    ->handler(function (Channel $channel, $matches) {
        $user = last($matches);

        $data = Web::api('twitter/user', ['user' => $user]);
        $followers = number_format($data['followers_count']);
        $tweets = number_format($data['statuses_count']);

        $data = [
            "<cyan>@{$data['screen_name']}</cyan>",
            (!empty($data['location']) ? "<light_cyan>{$data['location']}</light_cyan>" : ''),
            "<cyan>{$data['description']}</cyan>",
            "<orange>{$followers} followers</orange>",
            "<orange>{$tweets} tweets</orange>",
        ];

        $channel->message('[ Twitter ] '.implode(' - ', array_filter($data)));

        return false;
    });
