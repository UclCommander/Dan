<?php namespace Plugins\Commands\Command;

use Dan\Irc\Channel;
use Dan\Irc\User;
use Plugins\Commands\CommandInterface;

class Hash implements CommandInterface {

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
        $msg = explode(' ', $message, 2);

        if(count($msg) != 2)
            return;

        if(in_array($msg[0], hash_algos()))
            $channel->sendMessage(hash($msg[0], $msg[1]));
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
        $user->sendNotice("hash <algo> <text> - Hashes <text> with <algo>");
        $user->sendNotice("See http://php.net/manual/en/function.hash.php and http://php.net/manual/en/function.hash-algos.php for more information");
    }}