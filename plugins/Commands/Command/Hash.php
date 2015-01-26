<?php namespace Plugins\Commands\Command;

use Dan\Contracts\CommandContract;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

class Hash implements CommandContract {

    /**
     * @inheritdoc
     */
    public function run(Channel $channel, User $user, $message)
    {
        $msg = explode(' ', $message, 2);

        if(count($msg) != 2)
            return;

        if(in_array($msg[0], hash_algos()))
            $channel->sendMessage(hash($msg[0], $msg[1]));
    }

    /**
     * @inheritdoc
     */
    public function help(User $user, $message)
    {
        $user->sendNotice("hash <algo> <text> - Hashes <text> with <algo>");
        $user->sendNotice("See http://php.net/manual/en/function.hash.php and http://php.net/manual/en/function.hash-algos.php for more information");
    }
}