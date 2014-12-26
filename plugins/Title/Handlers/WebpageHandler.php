<?php namespace Plugins\Title\Handlers;

use Dan\Irc\Channel;
use Plugins\Title\Handler;
use Plugins\Title\HandlerInterface;

class WebpageHandler extends Handler implements HandlerInterface {

    protected $contentType = [
        'text/html'
    ];

    /**
     * @inheritdoc
     */
    public function handleLink(Channel $channel, array $headers, $link)
    {
        $data = file_get_contents($link);
        $data = mb_convert_encoding($data, 'HTML-ENTITIES', "UTF-8");

        $dom = new \DOMDocument();
        $dom->strictErrorChecking = false;
        @$dom->loadHTML($data);
        $xpath = new \DOMXPath($dom);

        $title = $xpath->query("//title");

        if ($title->length == 0)
            return;

        $cleantitle = str_replace(["\r", "\n", "\t"], '', trim($title->item(0)->textContent));

        $channel->sendMessage("{reset}[{cyan} {$cleantitle} {reset}]");
    }
}