<?php namespace Plugins\Commands\Command;

use Dan\Core\Dan;
use Dan\Irc\Channel;
use Dan\Irc\Support;
use Dan\Irc\User;
use Plugins\Commands\CommandInterface;

class Part implements CommandInterface{

    public function run(Channel $channel, User $user, $message)
    {
        $cmd        = explode(' ', $message);
        $partFrom   = $cmd[0];
        $msg        = @$cmd[1];

        if(!in_array(substr($cmd[0], 0, 1), Support::get('CHANTYPES')))
        {
            $partFrom = $channel->getName();
            $msg = $cmd[0];
        }

        Dan::getApp('irc')->partChannel($partFrom, $msg);
    }
}