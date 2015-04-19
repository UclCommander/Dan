<?php namespace Plugins\Commands\Commands;

use Dan\Commands\Command;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

class Hash extends Command {

    /**
     * @inheritdoc
     */
    public function run(Channel $channel, User $user, $message)
    {
        $msg = explode(' ', $message, 2);

        if(count($msg) != 2)
            return;
        
        if($msg[0] == 'bcrypt')
        {
            $channel->sendMessage(password_hash($msg[1], PASSWORD_BCRYPT));
            return;
        }

        if(in_array($msg[0], hash_algos()))
            $channel->sendMessage(hash($msg[0], $msg[1]));
    }

    /**
     * @inheritdoc
     */
    public function help(User $user, $message)
    {
        $user->sendNotice("hash <algo> <text> - Hashes <text> with <algo>");
        $user->sendNotice("See http://skycld.co/php-hash and http://skycld.co/php-algos for more information");
    }
}