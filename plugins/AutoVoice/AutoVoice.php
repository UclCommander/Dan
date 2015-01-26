<?php namespace Plugins\AutoVoice; 

use Dan\Contracts\PluginContract;
use Dan\Core\Dan;
use Dan\Events\EventArgs;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;
use Dan\Plugins\Plugin;

class AutoVoice extends Plugin implements PluginContract {

    protected $version     = '1.0';
    protected $author      = "UclCommander";
    protected $description = "AutoVoice plugin";

    public function register()
    {
        $this->listenForEvent('irc.packets.join', [$this, 'voiceUser']);
    }

    public function unregister()
    {
        parent::unregister();
    }

    /**
     * Handles voicing users.
     *
     * @param \Dan\Events\EventArgs $eventArgs
     */
    public function voiceUser(EventArgs $eventArgs)
    {
        /** @var Channel $channel */
        $channel    = $eventArgs->get('channel');
        /** @var User $channel */
        $user       = $eventArgs->get('user');

        $irc = Dan::service('irc');

        /** @var User $self */
        $self = $channel->getUser($irc->user->getNick());

        if($self == null)
            return;

        if($self->hasOneOf('hoaq'))
        {
            $irc->send('MODE', $channel->getName(), '+v', $user->getNick());

            if(!$self->hasMode('v'))
                $irc->send('MODE', $channel->getName(), '+v', $self->getNick());
        }
    }
}