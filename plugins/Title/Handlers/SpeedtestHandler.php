<?php namespace Plugins\Title\Handlers;

use Dan\Irc\Channel;
use Plugins\Title\Handler;
use Plugins\Title\HandlerInterface;

class SpeedtestHandler extends Handler implements HandlerInterface {

    protected $domains = [
        'www.speedtest.net'
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

        $download   = trim($xpath->query("//div[contains(@class, 'share-download')]/p")->item(0)->textContent);
        $upload     = trim($xpath->query("//div[contains(@class, 'share-upload')]/p")->item(0)->textContent);
        $ping       = trim($xpath->query("//div[contains(@class, 'share-ping')]/p")->item(0)->textContent);
        $stars      = trim($xpath->query("//div[contains(@class, 'share-isp')]//div[contains(@class, 'share-stars')]")->item(0)->textContent);
        $isp        = trim($xpath->query("//div[contains(@class, 'share-isp')]/p")->item(0)->textContent);
        $server     = trim($xpath->query("//div[contains(@class, 'share-server')]/p")->item(0)->textContent);

        $channel->sendMessage("\x03[\x0310 Ping:\x0311 {$ping} \x03|\x0310 Down:\x0311 {$download} \x03|\x0310 Up:\x0311 {$upload} \x03|\x037 Carrier: {$isp} \x03|\x038 Server: {$server} \x03|\x033 {$stars} \x03]");
    }
}