<?php

use Dan\Irc\Location\Channel;
use Dan\Support\DotCollection;
use Dan\Support\Web;

on('irc.message.public')
    ->name('youtube')
    ->match("/(?:.*)(?:www\.)?youtu(?:be\.com|\.be)\/(?:(?:watch\?v=)?([a-zA-Z0-9\-_]+)(?:[a-zA-Z0-9\&\=\?]+)?)(?:.*)/")
    ->settings([
        'format' => [
            'default' => 'video_title,channel_title,likes,views,published,duration',
            'options' => [
                'video_title', 'channel_title', 'likes',
                'views', 'published', 'duration', 'rating',
                'comment_count', 'favorite_count', 'dimension',
                'caption', 'definition', 'thumbnail',
            ]
        ]
    ])
    ->handler(function (Channel $channel, $matches, DotCollection $settings) {
        $return = null;

        foreach($matches[1] as $match) {
            $json = Web::api('youtube/get', ['video' => $match]);

            if (!is_array($json)) {
                continue;
            }

            $likes = number_format($json['statistics']['likeCount']);
            $dislikes = number_format($json['statistics']['dislikeCount']);
            $rating = "<green>+{$likes}</green>/<red>-{$dislikes}</red>";
            $views = number_format($json['statistics']['viewCount']);
            $channelTitle = htmlspecialchars_decode($json['snippet']['channelTitle']);
            $videoTitle = htmlspecialchars_decode($json['snippet']['title']);

            $d = new DateInterval($json['contentDetails']['duration']);
            $duration = $d->format('%H') == '00' ? $d->format('%I:%S') : $d->format('%H:%I:%S');

            $d = new DateTime($json['snippet']['publishedAt']);
            $published = $d->format('F j, Y');

            $data = [
                'video_title'   => "<cyan>{$videoTitle}</cyan>",
                'channel_title' => "<yellow>{$channelTitle}</yellow>",
                'published'     => "<cyan>{$published}</cyan>",
                'thumbnail'     => $json['snippet']['thumbnails']['default']['url'],
                'definition'    => "<red>{$json['contentDetails']['definition']}</red>",
                'caption'       => "<orange>{$json['contentDetails']['caption']}</orange>",
                'dimension'     => "<orange>{$json['contentDetails']['dimension']}</orange>",
                'duration'      => "<light_cyan>{$duration}</light_cyan>",
                'views'         => "<cyan>{$views} views</cyan>",
                'likes'         => "<green>+{$likes}</green>",
                'dislikes'      => "<red>-{$dislikes}</red>",
                'rating'        => $rating,
                'comment_count'  => number_format($json['statistics']['commentCount']),
                'favorite_count' => number_format($json['statistics']['favoriteCount']),
            ];

            $format = $settings->get('format.default');
            $enabled = [];

            foreach (explode(',', $format) as $item) {
                $enabled[$item] = $data[$item];
            }

            $channel->message("[ YouTube ] " . implode(' - ', $enabled));
            $return = false;
        }

        return $return;
    });