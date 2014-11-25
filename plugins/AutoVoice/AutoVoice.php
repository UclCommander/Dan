<?php namespace Plugins\AutoVoice; 

use Dan\Contracts\PluginContract;
use Dan\Core\Dan;
use Dan\Plugins\Plugin;

class AutoVoice extends Plugin implements PluginContract {

    public function register()
    {
        $this->addEvent('irc.packet.join', [$this, 'voiceUser']);
    }

    public function unregister()
    {
        parent::unregister();
    }

    /**
     * @param $event
     */
    public function voiceUser($event)
    {
        //TODO: prevent errors
        Dan::app('irc')->sendRaw("MODE {$event[0][0]} +v {$event[1]->getNick()}");
    }
}