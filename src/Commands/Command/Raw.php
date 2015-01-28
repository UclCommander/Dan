<?php namespace Dan\Commands\Command;

use Dan\Commands\Command;
use Dan\Core\Dan;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

class Raw extends Command {

    protected $defaultRank = 'S';

    /**
     * @inheritdoc
     */
    public function run(Channel $channel, User $user, $message)
    {
        $irc = Dan::service('irc');
        $irc->sendRaw($message);
    }

    /**
     * @inheritdoc
     */
    public function help(User $user, $message)
    {
        $user->sendNotice("raw <line> - Sends a raw IRC line");
    }
}