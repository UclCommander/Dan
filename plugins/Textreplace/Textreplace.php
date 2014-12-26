<?php namespace Plugins\TextReplace; 


use Dan\Contracts\PluginContract;
use Dan\Events\EventArgs;
use Dan\Irc\Channel;
use Dan\Irc\User;
use Dan\Plugins\Plugin;

class TextReplace extends Plugin implements PluginContract {

    /** @var array */
    protected $messages = [];


    public function register()
    {
        $this->listenForEvent('irc.packet.privmsg', [$this, 'doReplace'], 7);
    }


    public function doReplace(EventArgs $e)
    {
        //Ignore private messages from users
        if($e->channel == null)
            return null;

        $message    = $e->message;
        /** @var User $user */
        $user       = $e->user;
        /** @var Channel $channel */
        $channel    = $e->channel;

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
                    $e->channel->sendMessage("{reset}[ {cyan}{bold}{$data['user']->getNick()}{normal} ] {$newMessage}");

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