<?php namespace Dan\Commands\Command;

use Dan\Commands\Command;
use Dan\Core\Config;
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
                $usr = Dan::blacklist()->add($data[0]);
                $user->sendNotice("Added {$usr}");
                break;

            case 'remove':
                $usr = Dan::blacklist()->remove($data[0]);
                $user->sendNotice("Removed {$usr}");
                break;

            case 'level':
                $level = intval($data[0]);
                Config::set('dan.blacklist_level', $level);
                Config::save('dan');
                $user->sendNotice("Blacklist level set to {$level}");
                break;

            case 'help':
                $this->help($user, $message);
                break;

            case 'list':
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
        $user->sendNotice("blacklist list - Returns all blacklisted users");
        $user->sendNotice("blacklist level <level> - Sets the blacklist level. 0 = off, 1 = commands only, 2 = everything");
        $user->sendNotice("blacklist add <user> - Adds a user to the blacklist");
        $user->sendNotice("blacklist remove <user> - Removes a user from the blacklist");
    }
}