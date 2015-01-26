<?php namespace Plugins\Fun\Commands; 


use Dan\Contracts\CommandContract;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

class Urban implements CommandContract  {

    /**
     * @inheritdoc
     */
    public function run(Channel $channel, User $user, $message)
    {
        $msg = urlencode($message);

        $data = @file_get_contents("http://api.urbandictionary.com/v0/define?term={$msg}");

        if($data == null)
        {
            $channel->sendMessage("Error fetching definition");
            return;
        }

        $json       = json_decode($data, true);

        if($json['result_type'] == 'no_results')
        {
            $channel->sendMessage("{reset}[ {cyan}No definition found {reset}]");
            return;
        }

        $list       = $json['list'];
        $item       = $list[0];
        $cleanDef   = str_replace(["\n", "\r"], '', $item['definition']);

        $channel->sendMessage("{reset}[ {cyan}{$item['word']} {reset}| {yellow}{$cleanDef} {reset}| {green}+{$item['thumbs_up']}{reset}/{red}-{$item['thumbs_down']} {reset}]");
    }

    /**
     * @inheritdoc
     */
    public function help(User $user, $message)
    {
        // TODO: Implement help() method.
    }
}