<?php namespace Plugins\Title\Handlers; 


use Dan\Irc\Location\Channel;
use Plugins\Title\Handler;
use Plugins\Title\HandlerInterface;

class YoutubeHandler extends Handler implements HandlerInterface {

    protected $domains = [
        'youtu.be',
        'youtube.com',
        'www.youtube.com',
    ];

    /**
     * @param array  $headers
     * @param string $link
     * @return string
     */
    public function handleLink(Channel $channel, array $headers, $link)
    {
        $data = parse_url($link);

        if($data['host'] !== 'youtu.be')
        {
            $utub = [];
            parse_str($data['query'], $utub);
            $match = $utub['v'];
        }
        else
            $match = substr($data['path'], 1);


        $data = file_get_contents("http://gdata.youtube.com/feeds/api/videos/{$match}");

        if($data === false)
            return null;

        $xml = simplexml_load_string($data);

        $title  = $xml->title;
        $user   = $xml->author->name;

        $views = number_format((string)$xml->children('yt', true)->statistics->attributes()->viewCount);
        $media = $xml->children('media', true);
        $time  = (string)$media->children('yt', true)->attributes()->seconds;

        $seconds    = $time % 60;
        $minutes    = ($time / 60) % 60;
        $hours      = ($time / 3600) % 60;

        // THERE HAS TO BE A BETTER WAY THAN THIS
        if($seconds < 10) $seconds = "0{$seconds}";

        if($minutes < 1)  $minutes = "00";
        if($minutes < 10) $minutes = "0{$minutes}";

        if($hours < 1)  $hours = '00';
        if($hours < 10 && $hours > 1) $hours = "0{$hours}";

        $duration   = implode(':', [$hours, $minutes, $seconds]);
        $ratingData = $xml->children('gd', true)->rating->attributes();
        $average    = (((floor(floatval($ratingData->average) * 2) / 2) / 5) * 10);
        $likebar    = str_repeat("{green}+", $average) . str_repeat("{red}-", (10 - $average));

        $channel->sendMessage("{reset}[{cyan} {$title}{reset} |{yellow} {$user}{reset} | {$likebar} {reset}|{cyan} {$views} views{reset} |{light_cyan} {$duration}{reset} ]");
    }
}