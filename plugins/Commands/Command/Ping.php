<?php namespace Plugins\Commands\Command;

use Dan\Irc\Channel;
use Dan\Irc\User;
use Plugins\Commands\CommandInterface;

class Ping implements CommandInterface {

    public function run(Channel $channel, User $user, $message)
    {
        $channel->sendMessage('Ping Pong!');
    }
}