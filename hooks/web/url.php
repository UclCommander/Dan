<?php

use Dan\Helpers\Web;
use Dan\Irc\Location\Channel;
use Illuminate\Support\Collection;

$ignored = [
    '*speedtest.net', '*youtube.*', 'youtu.be', '*newegg.com', 'twitter.com'
];

$mime = [
    'text/html',
    'image/png',
    'image/jpeg',
    'image/jpg',
    'image/gif',
];


hook('title')
    ->regex("#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#")
    ->func(function(Collection $args) use($ignored, $mime) {
        $matches = $args->get('matches')[0];

        /** @var Channel $channel */
        $channel = $args->get('channel');

        foreach(array_unique($matches) as $match)
        {
            $url = get_final_url($match);

            $info = parse_url($url);

            foreach($ignored as $ignore)
                if(fnmatch($ignore, $info['host']))
                    continue 2;

            $headers    = getHeaders($url);
            $type       = is_array($headers['content-type']) ? reset($headers['content-type']) : $headers['content-type'];
            $mimeType   = explode(';', $type)[0];

            if(!in_array($mimeType, $mime))
                continue;

            if($mimeType == 'text/html')
            {
                $html = Web::get($url);

                $str = trim(preg_replace('/\s+/', ' ', $html));
                preg_match("/\<title\>(.*)\<\/title\>/i", $str, $title);

                $title = $title[1];
                $title = str_replace("\n", '', str_replace("\r", '', $title));
                $title = preg_replace('([ ]+)', ' ', trim($title));

                $title = html_entity_decode($title, ENT_QUOTES);

                if(empty($title))
                    continue;

                $channel->message("[ <cyan>{$title}</cyan> ]");
            }
            else // assume image
            {
                $type = $headers['content-type'];

                if(is_array($type))
                    $type = reset($type);

                $size   = isset($headers['content-length']) ? $headers['content-length'] : 0;
                $img    = getimagesize($match);
                $rez    = (count($img) > 1 ? "{$img[0]}x{$img[1]}" : '-');

                if(is_array($size))
                    $size = reset($size);

                $size = (!$size ? false : convert($size));

                $channel->message("[ <yellow>{$type}</yellow> |" . ($size ? " <cyan>{$size}</cyan> |" : '') . " <cyan>{$rez}</cyan> ]");
            }
        }

        return false;
    });