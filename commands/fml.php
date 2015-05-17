<?php

use Dan\Helpers\Web;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

/** @var User $user */
/** @var Channel $channel */
/** @var string $message */
/** @var string $entry */

if($entry == 'use')
{
    $data = Web::get("http://www.fmylife.com/random");

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

    message($channel, "{reset}[ {cyan}{$fml} {reset}| {green}+{$plus}{reset}/{red}-{$minus} {reset}]");

    unset($dom, $xpath);
}

if($entry == 'help')
{
    return [
        "fml - gets a random fml"
    ];
}