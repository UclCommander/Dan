<?php namespace Plugins\AutoVoice; 

use Dan\Contracts\PluginContract;
use Dan\Core\Dan;
use Dan\Events\EventArgs;
use Dan\Plugins\Plugin;

class AutoVoice extends Plugin implements PluginContract {

    public function register()
    {
        $this->listenForEvent('irc.packet.join', [$this, 'voiceUser']);
    }

    public function unregister()
    {
        parent::unregister();
    }

    /**
     * @param $event
     */
    public function voiceUser(EventArgs $event)
    {
        //TODO: prevent errors
        Dan::app('irc')->sendRaw("MODE {$event->channel->getName()} +v {$event->user->getNick()}");
    }
}