<?php namespace Plugins\Commands\Command; 

use Dan\Core\Dan;
use Dan\Irc\Channel;
use Dan\Irc\User;
use Plugins\Commands\CommandInterface;

class Join implements CommandInterface {

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
        $cmd = explode(' ', $message);
        Dan::app('irc')->joinChannel($cmd[0]);
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
        $user->sendNotice("join <channel> [password] - Joins <channel> with an optional [password]");
    }
}