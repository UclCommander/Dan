<?php namespace Dan\Irc; 


use Dan\Contracts\PacketContract;
use Dan\Contracts\ServiceContract;
use Dan\Core\Config;
use Dan\Core\Console;
use Dan\Core\ConsoleColor;
use Dan\Irc\Helpers\Color;
use Dan\Irc\Helpers\Parser;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\Location;
use Dan\Irc\Location\User;
use Dan\Sockets\Socket;
use Illuminate\Support\Collection;

class Connection implements ServiceContract {

    /** @var \Illuminate\Support\Collection */
    public $numeric;

    /** @var \Illuminate\Support\Collection */
    public $supported;

    /** @var \Illuminate\Support\Collection */
    public $config;

    /** @var User */
    public $user = null;

    /** @var \Illuminate\Support\Collection */
    protected $channels;

    /** @var bool */
    protected $running = false;

    /** @var Socket */
    protected $socket;

    /** @var Connection */
    protected static $self;


    /**
     * Gets the current instance of itself.
     *
     * @return Connection
     */
    public static function instance() { return static::$self; }

    /**
     * Connection class.
     */
    public function __construct()
    {
        static::$self = $this;

        $this->config       = Config::get('irc');
        $this->numeric      = new Collection();
        $this->supported    = new Collection();
        $this->channels     = new Collection();
        $this->user         = new User($this->config->get('nickname'), $this->config->get('username'), null, $this->config->get('realname'));
    }

    /**
     * Runs the bot.
     */
    public function run()
    {
        $this->startConnection();
        $this->sendLoginInformation();

        while($this->running)
        {
            $line = $this->socket->read();

            if(empty($line))
                continue;

            Console::text($line)->debug()->color(ConsoleColor::Cyan)->push();

            $this->handleLine($line);
        }
    }

    /**
     * Handles a raw IRC line.
     *
     * @param $line
     */
    public function handleLine($line)
    {
        $data = Parser::parseLine($line);

        $user       = null;
        $from       = $data['from'];
        $command    = $data['command'];

        if(count($from) == 3)
            $user = new User(...$from);

        $cmd = $command[0];

        array_shift($command);

        if($cmd == 'ERROR')
        {
            $this->running = false;
            Console::text("ERROR: {$command[0]}")->critical()->push();
        }

        $this->handlePacket($cmd, new PacketInfo([
            'from'      => $from,
            'user'      => $user,
            'command'   => $command
        ]));
    }

    /**
     * @param                     $name
     * @param \Dan\Irc\PacketInfo $packetInfo
     * @return null
     */
    public function handlePacket($name, PacketInfo $packetInfo)
    {
        $name = ucfirst(strtolower($name));

        $class = '\Dan\Irc\Packets\Packet' . $name;

        if (!class_exists($class))
        {
            Console::text("Cannot find packet handler for {$name}")->debug()->warning()->push();
            return;
        }

        /** @var PacketContract $handler */
        $handler = new $class();
        $handler->handle($this, $packetInfo);
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Sending functions
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Sends the USER login information on connection.
     */
    public function sendLoginInformation()
    {
        $this->send('USER', $this->config->get('nickname'), $this->config->get('username'), '*', $this->config->get('realname'));
        $this->sendNick();
    }


    /**
     * Sends a NICK command.
     *
     * @param string $nick
     */
    public function sendNick($nick = null)
    {
        if($nick == null)
            $nick = $this->config->get('nickname');

        $this->send('NICK', $nick);
    }

    /**
     * Sends a NOTICE to a location.
     *
     * @param Location $location
     * @param string $message
     */
    public function sendNotice(Location $location, $message)
    {
        $this->send("NOTICE", $location, $message);
    }


    /**
     * Sends a PRIVMSG to a location.
     *
     * @param Location $location
     * @param string   $message
     * @param bool     $colors
     */
    public function sendMessage(Location $location, $message, $colors = true)
    {
        if($colors)
            $message = Color::parse($message);

        $this->send("PRIVMSG", $location, $message);
    }

    /**
     * Sends information to the server.
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

            if($add instanceof Location)
                $add = (string)$param;

            // If it contains spaces, automatically add :
            if(strpos($add, ' ') > 0)
                $add = ':' . $add;

            if($add === "\n")
            {
                $this->sendRaw(implode(' ', $compiled));
                $compiled = [];
                continue;
            }

            $compiled[] = $add;
        }

        $this->sendRaw(implode(' ', $compiled));
    }

    /**
     * Sends a raw line.
     *
     * @param string $raw
     */
    public function sendRaw($raw)
    {
        if(!$this->running)
            return;

        Console::text("SENDING: {$raw}")->debug()->info()->push();

        $this->socket->send("{$raw}\r\n");
    }


    // -----------------------------------------------------------------------------------------------------------------
    // Channel functions
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Joins a channel.
     *
     * @param string $channel
     * @param string $key
     */
    public function joinChannel($channel, $key = null)
    {
        if($this->channels->has(strtolower($channel)))
            return;

        $this->send('JOIN', $channel, $key != null ? ":{$key}" : null);
    }

    /**
     * Adds a channel to the list.
     *
     * @param $name
     */
    public function addChannel($name)
    {
        $safe = strtolower($name);

        if($this->channels->has($safe))
            return;

        $this->channels->put($safe, new Channel($name));
    }

    /**
     * Gets a channel.
     *
     * @param $name
     * @return Channel|null
     */
    public function getChannel($name)
    {
        $name = strtolower($name);

        if(!$this->hasChannel($name))
            return null;

        return $this->channels->get($name);
    }

    /**
     * Checks to see if we're in a channel.
     *
     * @param $name
     * @return bool
     */
    public function hasChannel($name)
    {
        return $this->channels->has(strtolower($name));
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Socket functions
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Starts the socket connection.
     */
    public function startConnection()
    {
        if($this->running)
            return;

        Console::text('Starting Socket Reader..')->debug()->push();

        $this->socket = new Socket();
        $this->socket->init(AF_INET, SOCK_STREAM, 0);

        Console::text("Connecting to {$this->config->get('server')}:{$this->config->get('port')} ")->debug()->info()->push();

        if(($cnt = $this->socket->connect($this->config->get('server'), $this->config->get('port'))) === false)
            Console::text($this->socket->getLastErrorStr())->critical()->push();

        $this->running = true;
    }

    public function register() { }
    public function unregister() { }
}