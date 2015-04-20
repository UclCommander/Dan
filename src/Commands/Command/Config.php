<?php namespace Dan\Commands\Command;

use Dan\Commands\Command;
use Dan\Core\Config as Cfg;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

class Config extends Command {

    protected $defaultRank = 'S';

    protected $guarded = [
        'irc.password',
        'irc.channels',
        'dan.sudo_users',
    ];

    /**
     * Runs the command.
     *
     * @param \Dan\Irc\Location\Channel $channel
     * @param \Dan\Irc\Location\User $user
     * @param string $message
     */
    public function run(Channel $channel, User $user, $message)
    {
        if(empty($message))
        {
            $this->help($user, $message);
            return;
        }

        $data = explode(' ', $message);

        if(isset($data[1]))
        {
            if (in_array($data[1], $this->guarded))
            {
                $user->sendNotice('This value is guarded.');
                return;
            }
        }

        switch($data[0])
        {
            case 'reload':
                Cfg::load();
                $user->sendNotice("Config reloaded");
                break;

            /*case 'add':
                if(Cfg::add($data[1], $data[2]))
                    $user->sendNotice("{$data[2]} added the the config");
                else
                    $user->sendNotice("Config key {$data[1]} is not an array");
                break;*/

            case 'set':
                Cfg::set($data[1], $data[2]);
                $user->sendNotice("Config key {$data[1]} set to {$data[2]}");
                break;

            case 'get':
                $item = Cfg::get($data[1]);
                
                $user->sendNotice($data[1] . " : " . (is_array($item) ? implode(', ', $item->toArray()) : $item));
                break;

            case 'save':
                Cfg::save();
                $user->sendNotice("Temp configuration saved to file.");
                break;
        }
    }

    /**
     * Command help.
     *
     * @param \Dan\Irc\Location\User $user
     * @param                        $message
     * @return mixed|void
     */
    public function help(User $user, $message)
    {
        $user->sendNotice("config save - Saves current configuration to file");
        $user->sendNotice("config reload - Reloads the config");
        $user->sendNotice("config set <key> <value> - Sets a config value ");
        $user->sendNotice("config get - Gets a config value");
    }
}