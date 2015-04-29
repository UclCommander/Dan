<?php namespace Dan\Irc; 


use Dan\Helpers\Parser;
use Dan\Network\Socket;

class Connection {

    /** @var bool $running */
    protected $running = false;

    /** @var Socket $socket */
    protected $socket;


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
    public function message($location, $message)
    {

    }

    /**
     * @param $location
     * @param $message
     */
    public function notice($location, $message)
    {

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

        debug("{brown}>> {$raw}");

        $this->socket->write("{$raw}\r\n");
    }

    /**
     * Reads the connection.
     */
    protected function read()
    {
        $this->login();

        while($this->running)
        {
            $line = $this->socket->read();

            if(empty($line))
                continue;

            debug("{cyan}<< {$line}");

            $data = Parser::parseLine($line);

            $cmd    = $data['command'];
            $from   = $data['from'];

            if($cmd[0] == 'PING')
                $this->send("PONG", $cmd[1]);

            //$this->handleLine($line);
        }
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