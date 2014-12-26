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

        $ping       = trim($xpath->query("//div[contains(@class, 'share-ping')]/p")->item(0)->textContent);
        $download   = trim($xpath->query("//div[contains(@class, 'share-download')]/p")->item(0)->textContent);
        $upload     = trim($xpath->query("//div[contains(@class, 'share-upload')]/p")->item(0)->textContent);
        $isp        = trim($xpath->query("//div[contains(@class, 'share-isp')]/p")->item(0)->textContent);
        $server     = trim($xpath->query("//div[contains(@class, 'share-server')]/p")->item(0)->textContent);
        $stars      = trim($xpath->query("//div[contains(@class, 'share-isp')]//div[contains(@class, 'share-stars')]")->item(0)->textContent);

        $parsedPing     = "{cyan}Ping: {light_cyan}{$ping}";
        $parsedDownload = "{cyan}Down: {light_cyan}{$download}";
        $parsedUpload   = "{cyan}Up: {light_cyan}{$upload}";
        $parsedIsp      = "{orange}ISP: {$isp}";
        $parsedServer   = "{yellow}Server: {$server}";
        $parsedStars    = "{green}{$stars}";

        $channel->sendMessage("{reset}[ {$parsedPing} {reset}| {$parsedDownload} {reset}| {$parsedUpload} {reset}| {$parsedIsp} {reset}| {$parsedServer} {reset}| {$parsedStars}{reset} ]");
    }
}