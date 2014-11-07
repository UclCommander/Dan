<?php namespace Plugins\Commands\Command; 
use Dan\Core\Dan;
use Dan\Irc\Channel;
use Dan\Irc\User;
use Plugins\Commands\CommandInterface;

class Join implements CommandInterface{

    public function run(Channel $channel, User $user, $message)
    {
        $cmd = explode(' ', $message);
        Dan::getApp('irc')->joinChannel($cmd[0]);
    }
}