<?php

use Dan\Helpers\Web;

$regex  = "/(?:.*)(?:www\.)?youtu(?:be\.com|\.be)\/(?:(?:watch\?v=)?([a-zA-Z0-9\-_]+)(?:[a-zA-Z0-9\&\=\?]+)?)(?:.*)/";
$format = "{reset}[{cyan} {VIDEO_TITLE}{reset} |{yellow} {CHANNEL_TITLE}{reset} | {green}+{LIKES}{reset}/{red}-{DISLIKES} {reset}|{cyan} {VIEWS} views{reset} |{cyan} {PUBLISHED}{reset} |{light_cyan} {DURATION}{reset} ]";

hook(['regex' => $regex], function(array $eventData, array $matches) use($format) {

    $items = [];

    foreach($matches[1] as $match)
    {
        $json = Web::api('youtube/get', ['video' => $match]);

        if (!is_array($json))
            return null;

        $videoTitle     = $json['snippet']['title'];
        $channelTitle   = $json['snippet']['channelTitle'];
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
        $duration = $d->format('%H:%I:%S');

        $d = new DateTime($published);
        $published = $d->format('F j, Y');

        $items[] =  parseFormat($format, [
            'thumbnail'     => $thumbnail,
            'video_title'   => $videoTitle,
            'channel_title' => $channelTitle,
            'likes'         => $likeCount,
            'dislikes'      => $dislikeCount,
            'views'         => $viewCount,
            'published'     => $published,
            'definition'    => $definition,
            'caption'       => $caption,
            'dimension'     => $dimension,
            'duration'      => $duration,
            'comments'      => $commentCount,
            'favorites'     => $favoriteCount,
        ]);
    }

    return $items;
});