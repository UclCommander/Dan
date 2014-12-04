<?php namespace Plugins\Commands\Command;

use Dan\Core\Dan;
use Dan\Irc\Channel;
use Dan\Irc\User;
use Plugins\Commands\CommandInterface;

class Say implements CommandInterface {

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
        $data = explode(' ', $message, 2);

        if(count($data) != 2)
            return;

        Dan::app('irc')->sendMessage($data[0], $data[1]);
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
        $user->sendNotice("say <channel> <message>");
    }
}