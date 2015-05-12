<?php namespace Dan\Irc; 


use Dan\Contracts\PacketContract;
use Dan\Helpers\IrcColor;
use Dan\Helpers\Parser;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\Location;
use Dan\Irc\Location\User;
use Dan\Network\Socket;

class Connection {

    /** @var bool $running */
    protected $running = false;

    /** @var Socket $socket */
    protected $socket;

    /** @var User $self */
    protected $self;

    protected $channels = [];


    public function __construct()
    {
        $this->self = user([config('irc.user.nick'), config('irc.user.name'), '']);
    }

    /**
     *
     */
    public function start()
    {
        // this should never happen...
        if($this->running)
            return;

        debug("Starting the Socket connection...");

        $this->socket = new Socket(AF_INET, SOCK_STREAM, 0);

        $server = config('irc.server');
        $port   = config('irc.port');

        info("Connecting to {$server}:{$port}...");

        if(($cnt = $this->socket->connect($server, $port)) === false)
            critical($this->socket->getLastErrorStr(), true);

        info("Connected.");

        $this->running = true;

        $this->read();
    }

    /**
     * @param $location
     * @param $message
     */
    public function message($location, $message, $color = true)
    {
        if($location instanceof Location)
            $location = $location->getLocation();

        if($color)
            $message = IrcColor::parse($message);

        $this->send("PRIVMSG", $location, $message);
    }

    /**
     * @param $location
     * @param $message
     */
    public function notice($location, $message)
    {
        if($location instanceof Location)
            $location = $location->getLocation();

        $this->send("NOTICE", $location, $message);
    }

    /**
     * Sends NICK.
     *
     * @param $nick
     */
    public function nick($nick)
    {
        $this->send("NICK", $nick);
    }

    /**
     * Builds a line from params.
     *
     * @param ...$params
     */
    public function send(...$params)
    {
        $compiled = [];

        foreach($params as $param)
        {
            $add = $param;

            if($add == null)
                continue;

            // If it contains spaces, automatically add :
            if(strpos($add, ' ') !== false)
                $add = ":{$add}";

            $compiled[] = $add;
        }

        $this->raw(implode(' ', $compiled));
    }

    /**
     * Sends a RAW line to the server.
     *
     * @param $raw
     */
    public function raw($raw)
    {
        if(!$this->running)
            return;

        // Get only the first 510 characters to prevent overflow issues
        $raw = substr($raw, 0, 510);

        $console = str_replace(config('irc.user.pass'), '[PASSWORD]', $raw);

        debug("{brown}>> {$console}");

        $this->socket->write("{$raw}\r\n");
    }

    /**
     * Am I in the channel?
     *
     * @param $channel
     * @return bool
     */
    public function inChannel($channel)
    {
        return array_key_exists($channel, $this->channels);
    }

    /**
     * Gets a channel.
     *
     * @param $channel
     * @return Channel
     */
    public function getChannel($channel)
    {
        return $this->channels[$channel];
    }

    /**
     * Adds a channel.
     *
     * @param $channel
     */
    public function addChannel($channel)
    {
        $this->channels[$channel] = new Channel($channel);
    }

    /**
     * Joins a channel.
     *
     * @param $channel
     */
    public function joinChannel($channel)
    {
        $this->send("JOIN", $channel);
    }

    /**
     * Parts a channel.
     *
     * @param $channel
     * @param string $reason
     * @return bool
     */
    public function partChannel($channel, $reason = 'Requested')
    {
        if(!$this->inChannel($channel))
            return false;

        $this->send("PART", $channel, $reason);

        return true;
    }

    //
    //
    //

    /**
     * Reads the connection.
     */
    protected function read()
    {

        $this->login();

        while ($this->running)
        {
            $line = $this->socket->read();

            if (empty($line))
            {
                continue;
            }

            $line = event('connection.line', $line);

            if (empty($line))
            {
                continue;
            }

            debug("{cyan}<< {$line}");

            try
            {
                $this->handleLine($line);
            }
            catch (\Exception $exception)
            {
                if($this->inChannel(config('dan.control_channel')))
                {
                    $this->message(config('dan.control_channel'), "Exception was thrown. {$exception->getMessage()} File: " . relative($exception->getTrace()[0]['file']) . "@{$exception->getLine()}");
                }
            }
        }
    }

    /**
     * @param $line
     */
    protected function handleLine($line)
    {
        $data = Parser::parseLine($line);

        $cmd    = $data['command'];
        $from   = $data['from'];

        $data = $cmd;

        array_shift($data);

        $normal = ucfirst(strtolower($cmd[0]));

        $class = "Dan\\Irc\\Packets\\Packet{$normal}";


        if(!class_exists($class))
        {
            debug("{red}Unable to find packet handler for {$normal}");
            return;
        }

        /** @var PacketContract $handler */
        $handler = new $class();

        $handler->handle($from, $data);

    }

    /**
     * Sends USER and NICK.
     */
    protected function login()
    {
        $this->send('USER', config('irc.user.name'), config('irc.user.name'), '*', config('irc.user.real'));
        $this->nick(config('irc.user.nick'));
    }

}