<?php namespace Dan\Irc; 

use Dan\Core\Config;
use Dan\Core\Console;
use Dan\Core\ConsoleColor;
use Dan\Contracts\ConnectionContract;
use Dan\Events\Event;
use Dan\Sockets\Socket;
use Illuminate\Support\Collection;

class Connection extends PacketHandler implements ConnectionContract {

    /** @var Collection $numeric */
    public $numeric;

    /** @var array|mixed $config */
    public $config   = [];

    /** @var Socket $socket  */
    protected $socket;

    /** @var bool $running */
    protected $running = false;

    /** @var int $sentLines */
    protected $sentLines = 0;

    /** @var int $receivedLines */
    protected $receivedLines = 0;

    /** @var \Illuminate\Support\Collection  */
    protected $channels;


    /**
     * Create the connection..
     */
    public function __construct()
    {
        $this->config   = new Collection(Config::get('irc'));
        $this->numeric  = new Collection();
        $this->channels = new Collection();
    }

    /**
     * Sets up the connection and runs it.
     */
    public function init()
    {
        if($this->running)
            return;

        Console::text('Starting Socket Reader..')->debug()->push();

        $this->socket = new Socket();
        $this->socket->init(AF_INET, SOCK_STREAM, 0);

        Console::text("Connecting to {$this->config['server']}:{$this->config['port']} ")->debug()->info()->push();

        if(($cnt = $this->socket->connect($this->config['server'], $this->config['port'])) === false)
            die($this->socket->getLastErrorStr());

        $this->run();
    }

    /**
     * Run the socket reader.
     */
    public function run()
    {
        $this->running = true;

        //Do we have a server password?
        if(isset($this->config['server_pass']))
            $this->sendRaw("PASS {$this->config['server_pass']}");

        $this->sendRaw("USER {$this->config['username']} {$this->config['username']} * :{$this->config['realname']}");
        $this->setNick($this->config['nickname']);

        while($this->running)
        {
            $line = $this->socket->read();

            //If it's an empty line (how do we get these?) bail.
            if(trim($line) == null)
                continue;

            Console::text($line)->debug()->color(ConsoleColor::Cyan)->push();

            $this->receivedLines++;

            $data   = Parser::parseLine($line);
            $cmd    = $data['cmd'];
            $user   = $data['user'];

            //Just incase we get errors from the parser.
            if(count($cmd) == 0)
                continue;

            $packet = ucfirst(strtolower($cmd[0]));

            $classStr = "Dan\\Irc\\Packets\\Packet{$packet}";

            if(!class_exists($classStr))
            {
                Console::text("Cannot find packet handler for {$cmd[0]}")->debug()->warning()->push();
                continue;
            }

            array_shift($cmd);

            /** @var Packet $class */
            $class              = new $classStr;
            $packetData         = [];
            $packetData['data'] = $cmd;

            if(!empty($user))
                $packetData['user'] = $user;

            $class->handlePacket($this, new PacketInfo($packetData));
        }
    }

    /*
     * -----------------------------------------------------------------------------------
     * Sending functions
     * -----------------------------------------------------------------------------------
     */

    /**
     * Sets the nickname
     *
     * @param $nick
     */
    public function setNick($nick)
    {
        if(Support::get('NICKLEN') !== false)
            $nick = substr($nick, 0, Support::get('NICKLEN'));

        $this->sendRaw("NICK {$nick}");
    }

    /**
     * Sends raw line(s) to the server.
     *
     * @param $lines
     */
    public function sendRaw(...$lines)
    {
        //Not running? Bail out.
        if(!$this->running)
            return;

        $this->sentLines++;

        foreach($lines as $line)
        {
            Console::text("SENDING: {$line}")->info()->debug()->push();

            foreach(explode("\n", $line) as $s)
                $this->socket->send("{$s}\r\n");
        }
    }

    /**
     * Sends a message to the given location
     *
     * @param $location
     * @param $message
     */
    public function sendMessage($location, ...$message)
    {
        foreach($message as $msg)
        {
            $msg = Color::parse($msg);
            $this->sendRaw("PRIVMSG {$location} :{$msg}");
        }
    }


    /**
     * Sends a notice
     *
     * @param $location
     * @param $message
     */
    public function sendNotice($location, ...$message)
    {
        foreach($message as $msg)
            $this->sendRaw("NOTICE {$location} :{$msg}");
    }

    /**
     * Joins a channel.
     *
     * @param $channel
     * @param null $password
     */
    public function joinChannel($channel, $password = null)
    {
        if(count($this->channels) > Support::get('MAXCHANNELS'))
        {
            Console::text("Cannot join channel. Maximum number of channels allowed to join is " . Support::get('MAXCHANNELS') . ".")->alert()->push();
            return;
        }

        if(!in_array(substr($channel, 0, 1), Support::get('CHANTYPES')))
        {
            Console::text("Invalid channel type.")->alert()->push();
            return;
        }

        $this->sendRaw("JOIN {$channel}" . ($password != '' ? " :{$password}" : ''));
    }

    /**
     * Parts a channel.
     *
     * @param        $channel
     * @param string $reason
     */
    public function partChannel($channel, $reason = "Bye")
    {
        if(!in_array(substr($channel, 0, 1), Support::get('CHANTYPES')))
        {
            Console::text("Invalid channel type.")->alert()->push();
            return;
        }

        $this->sendRaw("PART {$channel} :{$reason}");
    }

    /*
     * -----------------------------------------------------------------------------------
     * Channel functions
     * -----------------------------------------------------------------------------------
     */

    /**
     * Add a channel to the list if it doesn't exist.
     *
     * @param $name
     * @return \Dan\Irc\Channel
     */
    public function addChannel($name)
    {
        if(array_key_exists(strtolower($name), $this->channels))
            return $this->channels[strtolower($name)];

        $channel = new Channel($name);

        $this->channels[strtolower($name)] = $channel;

        return $channel;
    }

    /**
     * Gets a channel.
     *
     * @param $name
     * @return \Dan\Irc\Channel|null
     */
    public function getChannel($name)
    {
        if(array_key_exists(strtolower($name), $this->channels))
            return $this->channels[strtolower($name)];

        return null;
    }
}
 