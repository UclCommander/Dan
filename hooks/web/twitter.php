<?php


use Dan\Helpers\Web;
use Illuminate\Support\Collection;

hook('twitter_tweet')
    ->regex("/https?:\/\/twitter\.com\/([a-zA-Z0-9]+)\/status\/([0-9]{0,18})/")
    ->func(function(Collection $args) {
        $status = last(last($args->get('matches')));

        if(!is_numeric($status))
            return false;

        $data = Web::api('twitter/tweet', ['id' => $status]);

        $text = str_replace(["\n"], ' ', $data['text']);

        $args->get('channel')->message("[ <yellow>@{$data['user']['screen_name']}</yellow> | <green>RT {$data['retweet_count']}</green> | <light_cyan>{$data['favorite_count']}★</light_cyan> | <cyan>{$text}</cyan> ]");

        return true;
    });

hook('twitter_user')
    ->regex("/https?:\/\/twitter\.com\/([a-zA-Z0-9]+)\/?/")
    ->func(function(\Illuminate\Support\Collection $args) {
        $user = last($args->get('matches'));
        
        $data = Web::api('twitter/user', ['user' => $user]);

        $followers = number_format($data['followers_count']);

        $args->get('channel')->message("[ <yellow>@{$data['screen_name']}</yellow> | <light_cyan>Location:</light_cyan> <yellow>{$data['location']}</yellow> | <yellow>{$followers}</yellow> <light_cyan>followers</light_cyan> | <yellow>{$data['statuses_count']}</yellow> <light_cyan>tweets</light_cyan> | <cyan>{$data['description']}</cyan> ]");

        return true;
    });