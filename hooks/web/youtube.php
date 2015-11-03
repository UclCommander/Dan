<?php

use Dan\Helpers\Web;
use Dan\Irc\Location\Channel;
use Illuminate\Support\Collection;

hook('youtube')
    ->regex("/(?:.*)(?:www\.)?youtu(?:be\.com|\.be)\/(?:(?:watch\?v=)?([a-zA-Z0-9\-_]+)(?:[a-zA-Z0-9\&\=\?]+)?)(?:.*)/")
    ->func(function(Collection $args) {
        /** @var Channel $channel */
        $channel    = $args->get('channel');
        $matches    = $args->get('matches');

        $return = null;

        foreach($matches[1] as $match)
        {
            $json = Web::api('youtube/get', ['video' => $match]);

            if (!is_array($json))
                continue;

            $videoTitle     = htmlspecialchars_decode($json['snippet']['title']);
            $channelTitle   = htmlspecialchars_decode($json['snippet']['channelTitle']);
            $published      = $json['snippet']['publishedAt'];
            $thumbnail      = $json['snippet']['thumbnails']['default']['url'];

            $definition = $json['contentDetails']['definition'];
            $caption    = $json['contentDetails']['caption'];
            $dimension  = $json['contentDetails']['dimension'];
            $duration   = $json['contentDetails']['duration'];

            $viewCount      = number_format($json['statistics']['viewCount']);
            $likeCount      = number_format($json['statistics']['likeCount']);
            $dislikeCount   = number_format($json['statistics']['dislikeCount']);
            $commentCount   = number_format($json['statistics']['commentCount']);
            $favoriteCount  = number_format($json['statistics']['favoriteCount']);

            $d = new DateInterval($duration);
            $duration = $d->format('%H') == '00' ? $d->format('%I:%S') : $d->format('%H:%I:%S');

            $d = new DateTime($published);
            $published = $d->format('F j, Y');

            $enabled = [
                "<cyan>{$videoTitle}</cyan>",
                "<yellow>{$channelTitle}</yellow>",
                "<green>+{$likeCount}</green>/<red>-{$dislikeCount}</red>",
                "<cyan>{$viewCount} views</cyan>",
                "<cyan>{$published}</cyan>",
                "<light_cyan>{$duration}</light_cyan>",
            ];

            $channel->message("[ " . implode(' | ', $enabled) . " ]");

            $return = false;
        }

        return $return;
    });