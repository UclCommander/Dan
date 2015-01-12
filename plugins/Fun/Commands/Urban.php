<?php namespace Plugins\Fun\Commands; 


use Dan\Irc\Channel;
use Dan\Irc\User;
use Plugins\Commands\CommandInterface;

class Urban implements CommandInterface  {

    /**
     * Runs the command.
     *
     * @param \Dan\Irc\Channel $channel
     * @param \Dan\Irc\User    $user
     * @param                  $message
     * @return void
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
        $list       = $json['list'];
        $item       = $list[0];
        $cleanDef   = str_replace(["\n", "\r"], '', $item['definition']);

        $channel->sendMessage("{reset}[ {cyan}{$item['word']} {reset}| {yellow}{$cleanDef} {reset}| {green}+{$item['thumbs_up']}{reset}/{red}-{$item['thumbs_down']} {reset}]");
    }

    /**
     * Command help.
     *
     * @param \Dan\Irc\User $user
     * @param               $message
     * @return mixed
     */
    public function help(User $user, $message)
    {
        // TODO: Implement help() method.
    }
}