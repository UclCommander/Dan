<?php namespace Plugins\Commands\Commands;


use Dan\Commands\Command;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;
use Dan\Irc\MessageBuilder;

class Urban extends Command {

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

        $channel->sendMessage(function(MessageBuilder $builder) use ($item, $cleanDef) {
            $builder->required("{reset}[ {yellow}{$item['word']} {reset}| {cyan}");
            $builder->message($cleanDef);
            $builder->required(" {reset}| {green}+{$item['thumbs_up']}{reset}/{red}-{$item['thumbs_down']} {reset}]");

            $builder->overflowMessage("{reset}[{cyan} Read more: {$item['permalink']} {reset}]");
        });
    }

    /**
     * @inheritdoc
     */
    public function help(User $user, $message)
    {
        $user->sendNotice('urban <text> - Searches the only dictionary known to man.');
    }
}