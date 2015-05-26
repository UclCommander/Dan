<?php namespace Dan\Irc; 


use Dan\Console\Console;
use Dan\Contracts\PacketContract;
use Dan\Helpers\IrcColor;
use Dan\Helpers\Parser;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\Location;
use Dan\Irc\Location\User;
use Dan\Network\Socket;
use Illuminate\Support\Collection;

class Connection {

    /** @var bool $running */
    protected $running = false;

    /** @var Socket $socket */
    protected $socket;

    /** @var User $self */
    protected $self;

    protected $channels = [];

    protected $numeric;

    protected $attached = '';


    public function __construct()
    {
        $this->self = user([config('irc.user.nick'), config('irc.user.name'), '']);

        $this->numeric = new Collection();
    }

    /**
     * Gets a self instance of the bot as user.
     *
     * @return \Dan\Irc\Location\User
     */
    public function user()
    {
        return $this->self;
    }

    /**
     * Sets a numeric.
     *
     * @param $number
     * @param $data
     */
    public function setNumeric($number, $data)
    {
        $this->numeric->put($number, $data);
    }

    public function getNumeric($number)
    {
        return $this->numeric->get($number);
    }

    /**
     * Starts the connection.
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

        $this->socket->connect($server, $port);

        //$this->socket->nonBlocking();

        info("Connected.");

        $this->running = true;

        $this->read();
    }

    /**
     * Sends a PRIVMSG
     *
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
     * Sends a NOTICE.
     *
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
        return array_key_exists(strtolower($channel), $this->channels);
    }

    /**
     * Gets a channel.
     *
     * @param $channel
     * @return Channel
     */
    public function getChannel($channel)
    {
        return $this->channels[strtolower($channel)];
    }

    /**
     * Gets all channels
     *
     * @return Channel[]
     */
    public function channels()
    {
        return $this->channels;
    }

    /**
     * Adds a channel.
     *
     * @param $channel
     */
    public function addChannel($channel)
    {
        $clean = strtolower($channel);
        $this->channels[$clean] = new Channel($channel);
    }

    /**
     * Joins a channel.
     *
     * @param $channel
     * @param string $key
     */
    public function joinChannel($channel, $key = null)
    {
        $this->send("JOIN", $channel, $key);
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
     * Sends USER and NICK.
     */
    protected function login()
    {
        $this->send('USER', config('irc.user.name'), config('irc.user.name'), '*', config('irc.user.real'));
        $this->nick(config('irc.user.nick'));
    }


    /**
     * Reads the connection.
     */
    protected function read()
    {
        $this->login();

        $stdin = fopen('php://stdin', 'r');

        stream_set_blocking($stdin, 0);

        while ($this->running)
        {
            $input = [$stdin, $this->socket->getSocket()];
            $write = null;
            $except = null;

            if(stream_select($input, $write, $except, 0) > 0)
            {
                foreach ($input as $resource)
                {
                    if ($resource == $stdin)
                    {
                        $this->handleConsole($resource);
                    }
                    else
                    {
                        $this->handleIRC($resource);
                    }
                }
            }
        }
    }

    protected function handleIRC($resource)
    {
        $lines = $this->socket->read();

        foreach($lines as $line)
        {
            $line = trim($line);

            if (empty($line))
                continue;

            $line = event('connection.line', $line);

            debug("{cyan}<< {$line}");

            try
            {
                $this->handleLine($line);
            }
            catch (\Exception $exception)
            {
                Console::exception($exception);

                if ($this->inChannel(config('dan.control_channel')))
                {
                    $this->message(config('dan.control_channel'), "Exception was thrown. {$exception->getMessage()} File: " . relative($exception->getTrace()[0]['file']) . "@{$exception->getLine()}");
                }
            }
        }
    }

    /**
     * Handles an IRC line.
     *
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
     * @param $resource
     */
    protected function handleConsole($resource)
    {
        $message = trim(fgets($resource));

        $data = explode(' ', $message, 2);

        switch($data[0])
        {
            case '/attach':
                $this->attached = $data[1];
                break;

            case '/raw':
                $this->raw($data[1]);
                break;

            default:
                if($this->attached != '')
                    $this->send('PRIVMSG', $this->attached, $message);

        }
    }
}