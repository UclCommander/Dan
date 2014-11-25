<?php namespace Plugins\Youtube; 

use Dan\Contracts\PluginContract;
use Dan\Events\EventArgs;
use Dan\Plugins\Plugin;

class Youtube extends Plugin implements PluginContract{

    public function register()
    {
        $this->addEvent('irc.packet.privmsg', [$this, 'getVideo'], 5);
    }

    /**
     * @param \Dan\Events\EventArgs $event
     * @return bool
     */
    public function getVideo(EventArgs $event)
    {
        $message = $event->message;

        $matches = [];

        preg_match_all('/\b(?:https?\:\/\/)?(?:www\.)?youtu(?:\.be|be\.com)\/(?:watch\?v=)?([a-zA-Z0-9-]+)/i', $message, $matches);

        if(count($matches[1]) == 0)
            return null;

        // Remove duplicates.
        $videos = array_unique($matches[1]);

        foreach($videos as $match)
        {
            $data = file_get_contents("http://gdata.youtube.com/feeds/api/videos/{$match}");
            $xml = simplexml_load_string($data);

            $title  = $xml->title;
            $user   = $xml->author->name;

            $views = number_format((string)$xml->children('yt', true)->statistics->attributes()->viewCount);

            $media = $xml->children('media', true);
            $time = (string)$media->children('yt', true)->attributes()->seconds;

            $seconds    = $time % 60;
            $minutes    = ($time / 60) % 60;
            $hours      = ($time / 3600) % 60;

            if($seconds < 10)
                $seconds = "0{$seconds}";

            if($minutes < 10)
                $minutes = "0{$minutes}";

            if($minutes < 1)
                $minutes = "00";

            if($hours < 1)
                $hours = '00';

            $duration = implode(':', [$hours, $minutes, $seconds]);

            $rating = (string)$xml->children('gd', true)->attributes()->average;

            $event->channel->sendMessage("[\x035 {$title}\x03 by\x038 {$user}\x03{$rating} -\x0310 {$views} views\x03 -\x0311 {$duration}\x03 ]");
        }

        return false;
    }
}