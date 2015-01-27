<?php namespace Dan\Commands\Command;

use Dan\Commands\Command;
use Dan\Core\Dan;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

class Join extends Command {

    protected $defaultRank = 'S';

    /**
     * @inheritdoc
     */
    public function run(Channel $channel, User $user, $message)
    {
        $cmd = explode(' ', $message);
        Dan::service('irc')->joinChannel($cmd[0]);
    }

    /**
     * @inheritdoc
     */
    public function help(User $user, $message)
    {
        $user->sendNotice("join <channel> [password] - Joins <channel> with an optional [password]");
    }
}