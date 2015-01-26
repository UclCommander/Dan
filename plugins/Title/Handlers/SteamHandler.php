<?php namespace Plugins\Title\Handlers;

use Dan\Irc\Location\Channel;
use Plugins\Title\Handler;
use Plugins\Title\HandlerInterface;

class SteamHandler extends Handler implements HandlerInterface {

    protected $domains = [
        'store.steampowered.com'
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

        $sale       = false; //THIS SHOULD NEVER BE FALSE!!!!
        $priceData  = '';
        $gameData   = [];

        $gameData['title']  = trim($xpath->query("//div[contains(@class, 'apphub_AppName')]")->item(0)->textContent);
        $gameData['tags']   = $xpath->query("//a[contains(@class, 'app_tag')]");

        //var_dump($xpath->query("//div[contains(@class, 'discount_block')]")->length);

        if($xpath->query("//div[contains(@class, 'discount_block')]")->length)
        {
            $sale = true;

            $gameData['original_price'] = trim($xpath->query("//div[contains(@class, 'discount_original_price')]")->item(0)->textContent);
            $gameData['sale_price']     = trim($xpath->query("//div[contains(@class, 'discount_final_price')]")->item(0)->textContent);
            $gameData['discount']       = trim($xpath->query("//div[contains(@class, 'discount_pct')]")->item(0)->textContent);
        }
        else
        {
            $gameData['original_price'] = trim($xpath->query("//div[contains(@class, 'game_purchase_price')]")->item(0)->textContent);
        }

        //var_dump($sale, $gameData);

        if($sale)
        {
            $priceData = "{$gameData['sale_price']} ({green}{$gameData['discount']}{cyan})";
        }
        else
        {
            $priceData = $gameData['original_price'];
        }

        $channel->sendMessage("{reset}[{cyan} {$gameData['title']}{reset} |{cyan} {$priceData}{reset}]");
    }
}