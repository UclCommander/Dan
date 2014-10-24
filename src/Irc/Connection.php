<?php namespace Dan\Irc; 


use Dan\Core\Config;
use Dan\Core\Console;
use Dan\Core\ConsoleColor;
use Dan\Core\Dan;
use Dan\Sockets\Socket;

class Connection {

    /**
     * @var Socket
     */
    protected $socket;

    /**
     * @var bool
     */
    protected $running;

    /**
     * @var int
     */
    protected $sentLines = 0;

    /**
     * @var int
     */
    protected $recivedLines = 0;


    public function __construct()
    {
        $this->config = Config::get('irc');
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


    public function run()
    {
        $this->running = true;

        //Do we have a server password?
        if(isset($this->config['server_pass']))
            $this->sendRaw("PASS {$this->config['server_pass']}");

        $this->sendRaw("USER {$this->config['username']} {$this->config['username']} * :{$this->config['realname']}");
        $this->sendRaw("NICK {$this->config['nickname']}");

        while($this->running)
        {
            $line = $this->socket->read();

            //If it's an empty line (how do we get these?) bail.
            if(trim($line) == null)
                continue;

            Console::text($line)->debug()->color(ConsoleColor::Cyan)->push();

            $this->recivedLines++;

            $data = Parser::parseLine($line);
            $cmd = $data['cmd'];
            $user = new User($data['user']);

            //Just incase we get errors from the parser.
            if(count($cmd) == 0)
                continue;

            if($cmd[0] == 'ERROR')
            {
                Console::text("BREAKING OUT OF READER ({$line})")->warning()->push();
                break;
            }

            $packetClass = "Dan\\Irc\\Packets\\Packet" . ucfirst(strtolower($cmd[0]));

            //Check for the packet class.
            if(!class_exists($packetClass))
            {
                Console::text("Cannot find packet class for {$cmd[0]}")->debug()->warning()->push();
                continue;
            }

            $data = $cmd;
            array_shift($data);

            /** @var PacketInterface $packet */
            $packet = new $packetClass();
            $packet->run($this, $data, $user);
        }
    }



    /*
     * -----------------------------------------------------------------------------------
     * Sending functions
     * -----------------------------------------------------------------------------------
     */

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
            $this->sendRaw("PRIVMSG {$location} :{$msg}");
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
        Console::text("Joining channel {$channel}:{$password}")->debug()->info()->push();
        $this->sendRaw("JOIN {$channel}" . ($password != '' ? " :{$password}" : ''));
    }

}
 