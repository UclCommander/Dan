<?php namespace Plugins\Fun\Commands;

use Dan\Irc\Channel;
use Dan\Irc\User;
use Plugins\Commands\CommandInterface;

class Nbc implements CommandInterface {

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
        $channel->sendMessage("http://youtu.be/6qLz1L9YqIs");
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
        $user->sendNotice("Need help? Nobody cares.");
    }
}