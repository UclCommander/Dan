<?php namespace Plugins\TextReplace; 


use Dan\Contracts\PluginContract;
use Dan\Events\EventArgs;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;
use Dan\Plugins\Plugin;

class TextReplace extends Plugin implements PluginContract {

    /** @var array */
    protected $messages = [];


    public function register()
    {
        $this->listenForEvent('irc.packets.message.public', [$this, 'doReplace'], 7);
    }


    public function doReplace(EventArgs $eventArgs)
    {
        /** @var Channel $channel */
        $channel    = $eventArgs->get('channel');
        /** @var User $user */
        $user       = $eventArgs->get('user');
        $message    = $eventArgs->get('message');

        if(strpos($message, 's/') === 0)
        {
            $replace = explode('/', $message);

            if(count($replace) !== 3)
                return false;

            if(!array_key_exists($channel->getName(), $this->messages))
                return false;

            $arr = $this->messages[$channel->getName()];

            krsort($arr);

            foreach($arr as $time => $data)
            {
                if(strpos($data['message'], $replace[1]) !== false)
                {
                    $safe = preg_quote($replace[1]);

                    $this->messages[$channel->getName()][$time]['message'] = preg_replace("/{$safe}/", $replace[2], $data['message'], 1);

                    $newMessage = preg_replace("/{$safe}/", "{bold}{$replace[2]}{normal}", $data['message'], 1);
                    $channel->sendMessage("{reset}[ {cyan}{bold}{$data['user']->getNick()}{normal} ] {$newMessage}");

                    return false;
                }
            }

            $channel->sendMessage("Nothing found to replace!");
            return false;
        }

        foreach($this->messages as $chan => $lines)
        {
            if (count($lines) > 30)
            {
                array_shift($this->messages[$chan]);
            }
        }

        $this->messages[$channel->getName()][time()] = [
            'message'   => $message,
            'user'      => $user
        ];
    }
}