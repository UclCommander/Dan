<?php namespace Dan\Irc;

use Dan\Contracts\PacketContract;
use Dan\Contracts\SocketContract;
use Dan\Core\Dan;
use Dan\Helpers\DotCollection;
use Dan\Irc\Formatter\IrcOutputFormatter;
use Dan\Irc\Formatter\IrcOutputFormatterStyle;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\Location;
use Dan\Irc\Location\User;
use Dan\Network\Socket;
use Illuminate\Support\Collection;

class Connection implements SocketContract {

    /** @var string */
    protected $name;

    /** @var Socket $socket */
    protected $socket;

    /** @var Collection|Channel[] */
    public $channels = [];

    /** @var DotCollection  */
    public $config;

    /** @var DotCollection  */
    public $support;

    /** @var User  */
    public $user;

    /**
     * @param $name
     * @param array $config
     */
    public function __construct($name, array $config)
    {
        $this->name     = $name;
        $this->config   = new DotCollection($config);
        $this->support  = new DotCollection();
        $this->socket   = new Socket();
        $this->channels = new Collection();

        $this->user = new User([
            'nick'  => $this->config->get('user.nick'),
            'user'  => $this->config->get('user.name'),
            'real'  => $this->config->get('user.real'),
        ]);
    }

    /**
     * Gets the connection name.
     *
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    #region socket things

    /**
     * @throws \Exception
     */
    public function connect()
    {
        $server = $this->config->get('server');
        $port   = $this->config->get('port');

        info("Connecting to {$server}:{$port}...");
        $this->socket->connect($server, $port);
        success("Connected.");

        $this->login();
        return true;
    }

    /**
     *
     */
    public function getStream()
    {
        return $this->socket->getSocket();
    }

    /**
     * @param $resource
     */
    public function handle($resource)
    {
        $lines = $this->socket->read();

        foreach ($lines as $line) {
            $line = trim($line);

            if (empty($line)) {
                continue;
            }

            debug("[<magenta>{$this->name}</magenta>] >> {$line}");

            $this->handleLine($line);
        }
    }

    /**
     * @param $line
     */
    protected function handleLine($line)
    {
        $data = Parser::parseLine($line);

        $from = $data['from'];
        $cmd = $data['command'];

        $data = $cmd;
        array_shift($data);

        if ($cmd[0] == "ERROR") {
            warn("Disconnected from IRC");
            Dan::self()->removeSocket($this->name);

            return;
        }

        $normal = ucfirst(strtolower($cmd[0]));
        $class = "Dan\\Irc\\Packets\\Packet{$normal}";

        if (!class_exists($class)) {
            debug("<default>[ERROR]</default> [<red>{$this->name}</red>] <error>Unable to find packet handler for {$normal}</error>");

            return;
        }

        try {
            /** @var PacketContract $handler */
            $handler = new $class();
            $handler->handle($this, $from, $data);

            unset($handler);

        } catch (\Exception $exception) {
            error($exception->getMessage());

        } catch (\Error $error) {
            error($error->getMessage());
        }
    }

    #endregion

    #region irc things

    /**
     *
     */
    public function login()
    {
        $nick = $this->config->get('user.nick');
        $name = $this->config->get('user.name');
        $real = $this->config->get('user.real');

        $this->send('USER', $name, $name, '*', $real);
        $this->send("NICK", $nick);
    }

    /**
     * Joins a channel.
     *
     * @param $name
     * @param string $key
     * @throws \Exception
     */
    public function joinChannel($name, $key = '')
    {
        if (!in_array(substr($name, 0, 1), str_split($this->support->get('CHANTYPES')))) {
            throw new \Exception("Invalid channel prefix ".substr($name, 0, 1));
        }

        $this->send('JOIN', $name, $key);
    }

    /**
     * Joins a channel.
     *
     * @param $name
     * @param string $reason
     */
    public function partChannel($name, $reason = "Leaving")
    {
        if (!$this->inChannel($name)) {
            return;
        }

        $name = strtolower($name);

        $this->channels->forget($name);

        $this->send('PART', $name, $reason);
    }

    /**
     * Checks to see if the bot is in a channel.
     *
     * @param $channel
     * @return bool
     */
    public function inChannel($channel)
    {
        return $this->channels->has(strtolower($channel));
    }

    /**
     * Gets a channel if the bot is in it.
     *
     * @param $channel
     * @return \Dan\Irc\Location\Channel
     */
    public function getChannel($channel)
    {
        if (!$this->inChannel(strtolower($channel))) {
            return null;
        }

        return $this->channels->get(strtolower($channel));
    }

    /**
     * Gets a channel if the bot is in it.
     *
     * @param $channel
     * @return \Dan\Irc\Location\Channel
     */
    public function addChannel($channel)
    {
        if ($this->inChannel($channel)) {
            return;
        }

        $name = strtolower($channel);

        $this->channels->put($name, new Channel($this, $channel));
    }
    /**
     * Removes a channel.
     *
     * @param $channel
     * @return \Dan\Irc\Location\Channel
     */
    public function removeChannel($channel)
    {
        if (!$this->inChannel($channel)) {
            return;
        }

        $name = strtolower($channel);

        $this->channels->forget($name);
    }

    /**
     * @param $location
     * @param $message
     * @param array $styles
     * @throws \Exception
     */
    public function message($location, $message, $styles = [])
    {
        if (isChannel($location, $this->getName())) {
            if(!$this->inChannel($location)) {
                throw new \Exception("This channel doesn't exist.");
            }

            event('irc.bot.message.public', [
                'connection'    => $this,
                'user'          => $this->user,
                'channel'       => $location instanceof Channel ? $location : $this->getChannel($location),
                'message'       => $message
            ]);
        }

        if (!DEBUG) {
            console("[<magenta>{$this->name}</magenta>][[<cyan>{$location}</cyan>]][<yellow>{$this->user->nick()}</yellow>] {$message}");
        }

        $formatter = new IrcOutputFormatter(true);

        if (!empty($styles)) {
            foreach ($styles as $name => $style) {
                $formatter->setStyle($name, new IrcOutputFormatterStyle(...$style));
            }
        }

        $message = $formatter->format($message);

        $this->send('PRIVMSG', $location, $message);
    }

    /**
     * @param $location
     * @param $message
     * @param array $styles
     */
    public function action($location, $message, $styles = [])
    {
        $formatter = new IrcOutputFormatter(true);

        if (!empty($styles)) {
            foreach ($styles as $name => $style) {
                $formatter->setStyle($name, new IrcOutputFormatterStyle(...$style));
            }
        }

        $message = $formatter->format($message);

        $this->send('PRIVMSG', $location, "\001ACTION $message\001");
    }

    /**
     * @param $location
     * @param $message
     */
    public function notice($location, $message)
    {
        $this->send('NOTICE', $location, $message);
    }

    /**
     * Builds a line from params.
     *
     * @param array ...$params
     */
    public function send(...$params)
    {
        $compiled = [];

        for ($i = 0; $i < count($params); $i++) {
            $add = $params[$i];

            if(is_null($add)) {
                continue;
            }

            if($add instanceof Location) {
                $add = $add->getLocation();
            }

            if(is_array($add)) {
                $add = json_encode($add);
            }

            if(strpos($add, ' ') !== false) {
                $add = ":{$add}";
            }

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
        $raw = substr($raw, 0, 510);

        debug("[<magenta>{$this->name}</magenta>] << {$raw}");

        $this->socket->write("{$raw}\r\n");
    }

    #endregion

}