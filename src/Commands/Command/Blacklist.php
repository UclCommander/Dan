<?php namespace Dan\Commands\Command;

use Dan\Commands\Command;
use Dan\Core\Dan;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

class Blacklist extends Command {

    protected $defaultRank = 'S';

    /**
     * Runs the command.
     *
     * @param \Dan\Irc\Location\Channel $channel
     * @param \Dan\Irc\Location\User    $user
     * @param string $message
     */
    public function run(Channel $channel, User $user, $message)
    {
        $data   = explode(' ', $message);
        $cmd    = trim($data[0]);

        array_shift($data);

        switch($cmd)
        {
            case 'add':
                Dan::blacklist()->add($data[0]);
                break;

            case 'remove':
                Dan::blacklist()->remove($data[0]);
                break;

            default:
                $user->sendNotice("Users Blacklisted: " . implode(', ', Dan::blacklist()->all()));
                break;
        }
    }

    /**
     * Command help.
     *
     * @param \Dan\Irc\Location\User $user
     * @param  string $message
     * @return mixed
     */
    public function help(User $user, $message)
    {
        $user->sendNotice("blacklist - Returns all blacklisted users");
        $user->sendNotice("blacklist add <user> - Adds a user to the blacklist");
        $user->sendNotice("blacklist remove <user> - Removes a user from the blacklist");
    }
}