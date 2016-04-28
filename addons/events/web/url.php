<?php

use Dan\Irc\Location\Channel;
use Dan\Support\Url;
use Dan\Support\Web;

$ignored = [
    '*speedtest.net', '*youtube.*', 'youtu.be', '*newegg.com', 'twitter.com',
];

$mime = [
    'text/html',
    'image/png',
    'image/jpeg',
    'image/jpg',
    'image/gif',
];

on('irc.message.public')
    ->name('title')
    ->match("#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#")
    ->handler(function (Channel $channel, $matches) use ($ignored, $mime) {
        $urls = array_unique(head($matches));

        $titles = [];

        foreach ($urls as $url) {
            if (strpos($url, '.') === false) {
                continue;
            }
            
            $url = Url::getFinalUrl($url);

            $info = parse_url($url);

            foreach ($ignored as $ignore) {
                if (fnmatch($ignore, $info['host'])) {
                    continue 2;
                }
            }

            $headers = Url::getHeaders($url);
            $type = is_array($headers['content-type']) ? reset($headers['content-type']) : $headers['content-type'];
            $mimeType = explode(';', $type)[0];

            if (!in_array($mimeType, $mime)) {
                continue;
            }

            if ($mimeType == 'text/html') {
                $html = Web::get($url);

                preg_match("/\<title.*?\>(.*?)\<\/title\>/i", $html, $title);

                $title = $title[1] ?? null;

                if (empty($title)) {
                    continue;
                }

                $title = str_replace("\n", '', str_replace("\r", '', $title));
                $title = preg_replace('([ ]+)', ' ', trim($title));
                $title = html_entity_decode($title, ENT_QUOTES);

                if (in_array($title, $titles)) {
                    continue;
                }

                $titles[] = $title;

                $channel->message("[ Webpage ] {$title}");
            } elseif (strpos($mimeType, 'image') === 0) {
                $type = head(explode(';', $headers['content-type']));

                if (is_array($type)) {
                    $type = reset($type);
                }

                $size = isset($headers['content-length']) ? $headers['content-length'] : 0;
                $img = getimagesize($url);
                $rez = (count($img) > 1 ? "{$img[0]}x{$img[1]}" : '-');

                if (is_array($size)) {
                    $size = reset($size);
                }

                $size = (!$size ? false : convert($size));

                $type = strtoupper(last(explode('/', $type)));

                $compile = [
                    "<cyan>{$type}</cyan>",
                    (!$size ? '' : convert($size)),
                    "<orange>{$rez}</orange>",
                ];

                $channel->message('[ Image ] '.implode(' - ', array_filter($compile)));
            }
        }
    });
