<?php namespace Dan\Commands\Command;

use Dan\Commands\Command;
use Dan\Core\Dan;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

class Say extends Command {

    protected $defaultRank = 'S';

    /**
     * @inheritdoc
     */
    public function run(Channel $channel, User $user, $message)
    {
        $data = explode(' ', $message, 2);

        $irc = Dan::service('irc');

        if($irc->hasChannel($data[0]))
        {
            $irc->getChannel($data[0])->sendMessage($data[1]);
            return;
        }

        $channel->sendMessage($message);
    }

    /**
     * @inheritdoc
     */
    public function help(User $user, $message)
    {
        $user->sendNotice("say <message> - Sends <message> to the current channel");
        $user->sendNotice("say <channel> <message> - Sends <message> to <channel>");
    }
}