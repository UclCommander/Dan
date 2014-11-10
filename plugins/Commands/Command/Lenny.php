<?php namespace Plugins\Commands\Command;

use Dan\Irc\Channel;
use Dan\Irc\User;
use Plugins\Commands\CommandInterface;

class Lenny implements CommandInterface {

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
        $channel->sendMessage("( ͡° ͜ʖ ͡°)");
    }

    /**
     * Command help.
     *
     * @param \Dan\Irc\User $user
     * @param               $message
     *
     * @return mixed
     */
    public function help(User $user, $message)
    {
        $user->sendNotice("Give me da booty ( ͡° ͜ʖ ͡°)");
    }
}