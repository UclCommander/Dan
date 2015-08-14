<?php

use Dan\Helpers\Web;

$regex          = "#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#";
$format         = "{reset}[{cyan} {TITLE}{reset} ]";
$imageFormat    = "{reset}[{cyan} {TYPE} {reset}|{cyan} {SIZE} {reset}|{cyan} {RESOLUTION} {reset}]";

$ignored = [
    '*speedtest.net', '*youtube.*', 'youtu.be', 'github.com', '*newegg.com'
];

$mime = [
    'text/html',
    'image/png',
    'image/jpeg',
    'image/jpg',
    'image/gif',
];

hook(['regex' => $regex, 'name' => 'webpage'], function(array $eventData, array $matches) use($format, $imageFormat, $ignored, $mime) {

    $items = [];

    foreach($matches[0] as $match)
    {
        $new = get_final_url($match);

        $url = parse_url($new);

        if(fnmatch("*youtu*", $url['host']))
            return callHook('youtube', array_merge($eventData, ['message' => $new]));

        foreach($ignored as $ignore)
            if(fnmatch($ignore, $url['host']))
                continue 2;

        $headers    = getHeaders($match);
        $type       = is_array($headers['content-type']) ? reset($headers['content-type']) : $headers['content-type'];
        $mimeType   = explode(';', $type)[0];

        if(!in_array($mimeType, $mime))
            continue;

        debug($mimeType);

        if($mimeType == 'text/html')
        {
            $html = Web::get($match);

            $str = trim(preg_replace('/\s+/', ' ', $html));
            preg_match("/\<title\>(.*)\<\/title\>/i", $str, $title);

            $title = $title[1];
            $title = str_replace("\n", '', str_replace("\r", '', $title));
            $title = preg_replace('([ ]+)', ' ', trim($title));

            if(empty($title))
                continue;

            $items[] = parseFormat($format, [
                'title' => htmlspecialchars_decode(html_entity_decode($title)),
            ]);
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

            $items[] = parseFormat($imageFormat, [
                'type'          => $type,
                'file_type'     => last(explode('/', $type)),
                'size'          => (!$size ? 'unknown' : convert($size)),
                'resolution'    => $rez,
            ]);
        }
    }

    return $items;
});