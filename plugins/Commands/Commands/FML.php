<?php namespace Plugins\Commands\Commands;

use Dan\Commands\Command;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;


/**
 * Yes, I know there is an API. But it requires a key. I'm too lazy to use something I have to email for.
 */
class FML extends Command {

    /**
     * @inheritdoc
     */
    public function run(Channel $channel, User $user, $message)
    {
        $data = @file_get_contents("http://www.fmylife.com/random");

        if($data == null)
            return;

        $data = mb_convert_encoding($data, 'HTML-ENTITIES', "UTF-8");

        $dom = new \DOMDocument();
        $dom->strictErrorChecking = false;
        @$dom->loadHTML($data);
        $xpath = new \DOMXPath($dom);

        $articles = $xpath->query("//div[contains(@class, 'article')]");

        $rand = rand(0, $articles->length - 1);

        $item = $articles->item($rand);

        $fml    = $item->childNodes->item(0)->textContent;
        $plus   = $item->childNodes->item(1)->childNodes->item(1)->childNodes->item(0)->childNodes->item(0)->textContent;
        $minus  = $item->childNodes->item(1)->childNodes->item(1)->childNodes->item(0)->childNodes->item(2)->textContent;

        $plus = explode('(', $plus)[1];
        $plus = substr($plus, 0, strlen($plus) - 1);

        $minus = explode('(', $minus)[1];
        $minus = substr($minus, 0, strlen($minus) - 1);

        $channel->sendMessage("{reset}[ {cyan}{$fml} {reset}| {green}+{$plus}{reset}/{red}-{$minus} {reset}]");

        unset($dom, $xpath);
    }

    /**
     * @inheritdoc
     */
    public function help(User $user, $message)
    {
        $user->sendNotice("fml - gets a random fml");
    }
}