<?php namespace Plugins\TextReplace; 


use Dan\Contracts\PluginContract;
use Dan\Events\EventArgs;
use Dan\Plugins\Plugin;

class Textreplace extends Plugin implements PluginContract {

    /** @var array */
    protected $messages = [];


    public function register()
    {
        $this->addEvent('irc.packet.privmsg', [$this, 'doReplace'], 7);
    }


    public function doReplace(EventArgs $e)
    {
        $message    = $e->message;
        $user       = $e->user;
        $channel    = $e->channel->getName();

        if(strpos($message, 's/') === 0)
        {
            $replace = explode('/', $message);

            if(count($replace) !== 3)
                return false;

            if(!array_key_exists($channel, $this->messages))
                return false;

            $arr = $this->messages[$channel];

            krsort($arr);

            foreach($arr as $time => $data)
            {
                if(strpos($data['message'], $replace[1]) !== false)
                {
                    $save = preg_quotes($replace[1]);
                    $newMessage = preg_replace("/{$safe}/", $replace[2], $data['message'], 1);
                    $this->messages[$time]['message'] = $newMessage;
                    $e->channel->sendMessage("[{$data['user']->getNick()}] {$newMessage}");

                    return false;
                }
            }

            $e->channel->sendMessage("Nothing found to replace!");
            return false;
        }

        foreach($this->messages as $chan => $lines)
        {
            if (count($lines) > 30)
            {
                array_shift($this->messages[$chan]);
            }
        }

        $this->messages[$channel][time()] = [
            'message'   => $message,
            'user'      => $user
        ];
    }
}