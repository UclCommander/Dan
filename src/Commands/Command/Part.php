<?php namespace Dan\Commands\Command;

use Dan\Commands\Command;
use Dan\Core\Dan;
use Dan\Irc\Connection;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

class Part extends Command {

    protected $defaultRank = 'S';

    /**
     * @param \Dan\Irc\Location\Channel $channel
     * @param \Dan\Irc\Location\User $user
     * @param string $message
     */
    public function run(Channel $channel, User $user, $message)
    {
        $partFrom   = trim($message);

        if($partFrom == '')
            $partFrom = $channel->getName();

        /** @var Connection $irc */
        $irc = Dan::service('irc');

        if(!$irc->hasChannel($partFrom))
        {
            $user->sendNotice("I'm not in this channel!");
            return;
        }

        $irc->partChannel($partFrom, "Requested");
    }


    /**
     * @param \Dan\Irc\Location\User $user
     * @param string $message
     */
    public function help(User $user, $message)
    {
        $user->sendNotice("part - Parts the current channel");
        $user->sendNotice("part [channel] - Parts the given channel");
    }
}