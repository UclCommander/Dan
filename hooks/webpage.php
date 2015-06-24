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

hook(['regex' => $regex], function(array $eventData, array $matches) use($format, $imageFormat, $ignored, $mime) {

    $items = [];

    foreach($matches as $match)
    {
        $url = parse_url($match);

        foreach ($ignored as $ignore)
            if (fnmatch($ignore, $url['host']))
                continue 2;

        $headers    = get_headers($match, true);
        $type       = is_array($headers['Content-Type']) ? reset($headers['Content-Type']) : $headers['Content-Type'];
        $mimeType   = explode(';', $type)[0];

        var_dump($headers);

        if(!in_array($mimeType, $mime))
            continue;

        if($mimeType == 'text/html')
        {
            $html = Web::get($match);

            $str = trim(preg_replace('/\s+/', ' ', $html));
            preg_match("/\<title\>(.*)\<\/title\>/i", $str, $title);

            $title = $title[1];
            $title = str_replace("\n", '', str_replace("\r", '', $title));
            $title = preg_replace('([ ]+)', ' ', $title);

            $items[] = parseFormat($format, [
                'title' => $title,
            ]);
        }
        else // assume image
        {
            $type = $headers['Content-Type'];

            if(is_array($type))
                $type = reset($type);

            $size   = isset($headers['Content-Length']) ? convert($headers['Content-Length']) : '-';
            $img    = getimagesize($match);
            $rez    = (count($img) > 1 ? "{$img[0]}x{$img[1]}" : '-');

            $items[] = parseFormat($imageFormat, [
                'type'          => $type,
                'file_type'     => last(explode('/', $type)),
                'size'          => $size,
                'resolution'    => $rez,
            ]);
        }
    }

    return $items;
});