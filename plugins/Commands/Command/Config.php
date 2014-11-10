<?php namespace Plugins\Commands\Command;

Use Dan\Core\Config as Cfg;
use Dan\Irc\Channel;
use Dan\Irc\User;
use Plugins\Commands\CommandInterface;

class Config implements CommandInterface {

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
        $data = explode(' ', $message);

        switch($data[0])
        {
            case 'reload':
                Cfg::load();
                break;

            case 'set':
                Cfg::set($data[1], $data[2]);
                break;

            case 'get':
                $user->sendNotice($data[1] . " : " . Cfg::get($data[1]));
                break;
        }
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
        $user->sendNotice("config reload - Reloads the config");
        $user->sendNotice("config set <key> <value> - Sets a config value ");
        $user->sendNotice("config get - Gets a config value");
    }
}