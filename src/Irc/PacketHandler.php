<?php namespace Dan\Irc; 

use Dan\Core\Config;
use Dan\Core\Dan;
use Dan\Events\Event;
use Dan\Irc\Contracts\ConnectionContract;

abstract class PacketHandler implements ConnectionContract {

    /**
     * @var bool
     */
    private $is353 = false;

    /**
     * @var array
     */
    protected $numeric  = [];

    /**
     * @var array
     */
    protected $config   = [];

    /**
     * @var array
     */
    protected $motd   = [];

    /*
     * -----------------------------------------------------------------------------------
     * Packets.
     * -----------------------------------------------------------------------------------
     */

    public function packet001(array $data, User $user) { $this->numeric['001'] = $data; }
    public function packet002(array $data, User $user) {  $this->numeric['002'] = $data; }
    public function packet003(array $data, User $user) {  $this->numeric['003'] = $data; }
    public function packet004(array $data, User $user) {  $this->numeric['004'] = $data; }

    /**
     * Server support list.
     *
     * @param array $data
     * @param \Dan\Irc\User $user
     */
    public function packet005(array $data, User $user)
    {
        array_shift($data); // remove username from the list
        array_pop($data); //remove "are supported by this server"

        foreach($data as $s)
        {
            $kv = explode('=', $s, 2);

            $value = null;

            switch($kv[0])
            {
                case 'CMDS':
                    $value = explode(',',$kv[1]);
                    break;

                case 'CHANTYPES':
                    $value = str_split($kv[1]);
                    break;

                case 'PREFIX':
                    $matches = [];

                    if(preg_match("/\(([a-z]+)\)(.*)/", $kv[1], $matches))
                    {
                        array_shift($matches);

                        $value = [
                            str_split($matches[0]),
                            str_split($matches[1]),
                        ];
                    }

                    break;

                default:
                    $value = count($kv) == 2 ? $kv[1] : null;
            }

            Support::put($kv[0], $value);
        }
    }

    /**
     * Channel title packet.
     *
     * @param array $data
     * @param \Dan\Irc\User $user
     */
    public function packet332(array $data, User $user)
    {
        $channel = $this->getChannel($data[1]);

        if($channel == null)
            return;

        $channel->setTitle($data[2]);
    }

    /**
     * Channel title information packet.
     *
     * @param array $data
     * @param \Dan\Irc\User $user
     */
    public function packet333(array $data, User $user)
    {
        $channel = $this->getChannel($data[1]);

        if($channel == null)
            return;

        $channel->setTitleInfo($data[2], $data[3]);
    }

    /**
     * @param array $data
     * @param \Dan\Irc\User $user
     */
    public function packet353(array $data, User $user)
    {
        $channel = $this->getChannel($data[2]);

        if($channel == null)
            return;

        if(!$this->is353)
        {
            $this->is353 = true;
            $channel->clearUsers();
        }

        $channel->setNames(Parser::parseNames($data[3]));
    }

    /**
     * @param array $data
     * @param \Dan\Irc\User $user
     */
    public function packet366(array $data, User $user)
    {
        $this->is353 = false;
    }

    /**
     * @param array $data
     * @param \Dan\Irc\User $user
     */
    public function packet372(array $data, User $user)
    {
        $this->motd[] = $data;
    }

    /**
     * @param array $data
     * @param \Dan\Irc\User $user
     */
    public function packet376(array $data, User $user)
    {
        //If it's an unreal server, send +B for bots
        foreach($this->numeric['004'] as $d)
            if(strpos($d, 'Unreal3') === 0)
                $this->sendRaw("MODE {$this->config['nickname']} +B");


        foreach($this->config['channels'] as $autoJoinChannel)
        {
            $password = explode(':', $autoJoinChannel);
            $this->joinChannel($password[0], (isset($password[1]) ? $password[1] : null));
        }
    }

    /**
     * @param array $data
     * @param \Dan\Irc\User $user
     */
    public function packetJoin(array $data, User $user)
    {
        Event::fire('irc.packet.join', $data, $user);

        if($user->getNick() == Config::get('irc.nickname'))
        {
            $this->addChannel($data[0]);
        }
    }

    /**
     * @param array $data
     * @param \Dan\Irc\User $user
     */
    public function packetMode(array $data, User $user)
    {
        Event::fire('irc.packet.mode', $data, $user);
    }

    /**
     * @param array $data
     * @param \Dan\Irc\User $user
     */
    public function packetNick(array $data, User $user)
    {
        Event::fire('irc.packet.nick', $data, $user);
    }

    /**
     * @param array $data
     * @param \Dan\Irc\User $user
     */
    public function packetNotice(array $data, User $user)
    {
        Event::fire('irc.packet.notice', $data, $user);
    }

    /**
     * @param array $data
     * @param \Dan\Irc\User $user
     */
    public function packetPing(array $data, User $user)
    {
        $this->sendRaw("PONG {$data[0]}");
        Event::fire('irc.packet.ping', $data, $user);
    }

    /**
     * @param array $data
     * @param \Dan\Irc\User $user
     */
    public function packetPrivmsg(array $data, User $user)
    {
        Event::fire('irc.packet.privmsg', $data, $user);

        if($data[1] == "\001VERSION\001")
        {
            $this->sendNotice($user->getNick(), "\001VERSION Dan " . Dan::VERSION . " - PHP " . phpversion() . "\001");
            return;
        }
        else if($data[1] == "\001PING\001")
        {
            $this->sendNotice($user->getNick(), "\001PING " . time() . " \001");
            return;
        }
        else if($data[1] == "\001TIME\001")
        {
            $this->sendNotice($user->getNick(), "\001TIME " . date("r") . "\001");
            return;
        }

        $message = $data[1];

        if($message == '.users')
        {
            $channel = $this->getChannel($data[0]);

            $channel->sendMessage(json_encode($channel->getUsers()));
        }
        //$connection->sendMessage($data[0], $data[1]);
    }

    /**
     * @param array $data
     * @param \Dan\Irc\User $user
     */
    public function packetxxx(array $data, User $user)
    {

    }

}
 