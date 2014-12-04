<?php namespace Plugins\Fun\Commands;

use Dan\Irc\Channel;
use Dan\Irc\User;
use Plugins\Commands\CommandInterface;

class Trp implements CommandInterface {

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
        $channel->sendMessage("popcorn anyone? http://puu.sh/4JYlr/0b652af25d.jpg");
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
        $user->sendNotice("Gives popcorn.");
    }
}