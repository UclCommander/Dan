<?php namespace Plugins\Title;

use Dan\Contracts\PluginContract;
use Dan\Events\EventArgs;
use Dan\Plugins\Plugin;

class Title extends Plugin implements PluginContract {

    public function register()
    {
        $this->addEvent('irc.packet.privmsg', [$this, 'getTitle'], 4);
    }

    public function getTitle(EventArgs $event)
    {
        /** @var \Dan\Irc\Channel $channel */
        $channel = $event->channel;
        $message = $event->message;

        $match = [];

        preg_match_all('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', $message, $match);

        if(count($match) == 0)
            return null;

        $matches = $match[0];

        foreach($matches as $link)
        {
            $headers = get_headers($link);

            $type = '';
            $size = '';
            $rez  = '';

            foreach($headers as $header)
            {
                if(strpos(strtolower($header), 'content-length:') !== false)
                {
                    $size = " - " . $this->formatBytes(trim(explode(':', $header)[1]));
                    continue;
                }

                if(strpos(strtolower($header), 'content-type:') !== false)
                {
                    $type = trim(explode(';', trim(explode(':', $header)[1]))[0]);

                    if(strpos($header, 'image') !== false)
                    {
                        $img = getimagesize($link);
                        $rez = " - {$img[0]}x{$img[1]}";
                    }
                }

                if(strpos($header, 'HTTP/1.1 404 None') !== false || strpos($header, 'HTTP/1.1 404 Not Found') !== false)
                {
                    $channel->sendMessage("[\x035 404 Not Found \x03]");
                    continue;
                }
            }

            if($type == 'text/html' || $type == 'text/plain')
            {
                $data = file_get_contents($link);
                $data = mb_convert_encoding($data, 'HTML-ENTITIES', "UTF-8");

                $dom = new \DOMDocument();
                $dom->strictErrorChecking = false;
                @$dom->loadHTML($data);
                $xpath = new \DOMXPath($dom);

                $title = $xpath->query("//title");

                if($title->length == 0)
                    continue;

                $cleantitle = str_replace(["\r", "\n", "\t"], '', trim($title->item(0)->textContent));

                $channel->sendMessage("[\x035 {$cleantitle} \x03]");
            }
            else
            {
                $channel->sendMessage("[\x035 {$type}{$size}{$rez} \x03]");
            }
        }

        return false;
    }


    function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        // Uncomment one of the following alternatives
        $bytes /= pow(1024, $pow);
        //$bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}