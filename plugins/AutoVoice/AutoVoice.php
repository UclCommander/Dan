<?php namespace Plugins\AutoVoice; 

use Dan\Contracts\PluginContract;
use Dan\Core\Dan;
use Dan\Events\EventArgs;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;
use Dan\Plugins\Plugin;
use Illuminate\Support\Str;

class AutoVoice extends Plugin implements PluginContract {

    protected $version     = '1.0';
    protected $author      = "UclCommander";
    protected $description = "AutoVoice plugin";

    /**
     * Registers the plugin.
     */
    public function register()
    {
        $this->listenForEvent('irc.packets.join', [$this, 'voiceUser']);
        $this->listenForEvent('irc.packets.nick', [$this, 'checkUnidentified']);
    }

    /**
     * Unregisters the plugin.
     */
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
            if(!Str::contains($user->getNick(), 'Unidentified'))
                $irc->send('MODE', $channel->getName(), '+v', $user->getNick());

            if(!$self->hasMode('v'))
                $irc->send('MODE', $channel->getName(), '+v', $self->getNick());
        }
    }

    /**
     * Checks to see if the user is unidentified, if so, devoice.
     *
     * @param \Dan\Events\EventArgs $eventArgs
     */
    public function checkUnidentified(EventArgs $eventArgs)
    {
        $nick = $eventArgs->get('command');

        $mode = "+v";

        if(Str::contains($nick[0], 'Unidentified'))
            $mode = "-v";

        $irc = Dan::service('irc');

        $channels = $irc->getChannels();

        foreach($channels as $channel)
        {
            if(!$this->canVoice($channel))
                continue;

            $irc->send('MODE', $channel->getName(), $mode, $nick[0]);
        }
    }

    /**
     * Do I have permission to voice users?
     *
     * @param \Dan\Irc\Location\Channel $channel
     * @return bool
     */
    public function canVoice(Channel $channel)
    {
        $irc = Dan::service('irc');

        /** @var User $self */
        $self = $channel->getUser($irc->user->getNick());

        if($self == null)
            return false;

        return $self->hasOneOf('hoaq');
    }
}