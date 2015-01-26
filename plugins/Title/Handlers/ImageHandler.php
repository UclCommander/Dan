<?php namespace Plugins\Title\Handlers;

use Dan\Irc\Location\Channel;
use Plugins\Title\Handler;
use Plugins\Title\HandlerInterface;

class ImageHandler extends Handler implements HandlerInterface {

    protected $contentType = [
        'image/png',
        'image/jpeg',
        'image/jpg',
        'image/gif',
    ];

    /**
     * @inheritdoc
     */
    public function handleLink(Channel $channel, array $headers, $link)
    {
        $type   = $headers['content-type']['value'];
        $size   = isset($headers['content-length']['value']) ? " | " . $this->formatBytes($headers['content-length']['value']) : '';
        $img    = getimagesize($link);
        $rez    = (count($img) > 1 ? " | {$img[0]}x{$img[1]}" : '');

        $channel->sendMessage("\x03[\x0310 {$type}{$size}{$rez} \x03]");
    }
}