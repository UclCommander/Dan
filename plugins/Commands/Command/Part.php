<?php namespace Plugins\Commands\Command;

use Dan\Core\Dan;
use Dan\Irc\Channel;
use Dan\Irc\Support;
use Dan\Irc\User;
use Plugins\Commands\CommandInterface;

class Part implements CommandInterface {

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
        $cmd        = explode(' ', $message, 2);
        $partFrom   = $cmd[0];
        $msg        = @$cmd[1];

        if(!in_array(substr($cmd[0], 0, 1), Support::get('CHANTYPES')))
        {
            $partFrom = $channel->getName();
            $msg = $cmd[0];
        }

        Dan::getApp('irc')->partChannel($partFrom, $msg);
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
        $user->sendNotice("part - Parts the current channel");
        $user->sendNotice("part [message] - Parts the current channel with an optional [message]");
        $user->sendNotice("part [channel] [message] - Parts [channel] with an optional [message]");
    }
}