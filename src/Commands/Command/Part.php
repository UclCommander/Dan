<?php namespace Dan\Commands\Command;

use Dan\Commands\Command;
use Dan\Core\Dan;
use Dan\Irc\Connection;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

class Part extends Command {

    protected $defaultRank = 'S';

    /**
     * @inheritdoc
     */
    public function run(Channel $channel, User $user, $message)
    {
        $partFrom   = trim($message);

        /** @var Connection $irc */
        $irc = Dan::service('irc');

        if(!$irc->hasChannel($partFrom))
        {
            $user->sendMessage("Bot is not in the channel");
            return;
        }

        $irc->removeChannel($partFrom, "Requestion");
    }


    /**
     * @inheritdoc
     */
    public function help(User $user, $message)
    {
        $user->sendNotice("part - Parts the current channel");
        $user->sendNotice("part [channel] - Parts the given channel");
    }
}