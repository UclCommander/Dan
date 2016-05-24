<?php

namespace Dan\Irc;

use Dan\Contracts\ConnectionContract;
use Dan\Contracts\DatabaseContract;
use Dan\Contracts\PacketContract;
use Dan\Events\Event;
use Dan\Events\Traits\EventTrigger;
use Dan\Irc\Formatter\IrcOutputFormatter;
use Dan\Irc\Formatter\IrcOutputFormatterStyle;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\Location;
use Dan\Irc\Location\User;
use Dan\Irc\Traits\BotStaff;
use Dan\Irc\Traits\Helpers;
use Dan\Irc\Traits\IrcDatabase;
use Dan\Irc\Traits\Parser;
use Dan\Network\Exceptions\BrokenPipeException;
use Dan\Network\Socket;
use Illuminate\Support\Collection;

class Connection implements ConnectionContract, DatabaseContract
{
    use Parser, IrcDatabase, Helpers, EventTrigger, BotStaff;

    /**
     * @var \Illuminate\Support\Collection
     */
    public $config;

    /**
     * @var \Illuminate\Support\Collection
     */
    public $serverInfo;

    /**
     * @var \Illuminate\Support\Collection
     */
    public $supported;

    /**
     * @var User
     */
    public $user;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var \Dan\Network\Socket
     */
    protected $socket;

    /**
     * @var int
     */
    protected $reconnectCount = 0;

    /**
     * @var bool
     */
    protected $quitting = false;

    /**
     * @var Collection|Channel[]
     */
    public $channels;

    /**
     * @var bool
     */
    protected $slackLine = false;

    /**
     * Connection constructor.
     *
     * @param $name
     * @param $config
     */
    public function __construct($name, $config)
    {
        $this->name = $name;
        $this->config = dotcollect($config);

        $this->createDatabase();

        $this->user = new User($this, $this->config->get('user.nick'), $this->config->get('user.name'), null, $this->config->get('user.real'));
        $this->socket = new Socket();
        $this->supported = new Collection();
        $this->serverInfo = new Collection();
        $this->channels = new Collection();

        events()->subscribe('config.reload', function () {
            $this->config = dotcollect(config("irc.servers.{$this->name}"));

            if ($this->config->get('user.nick') !== $this->user->nick) {
                $this->send('NICK', $this->config->get('user.nick'));
            }
        }, Event::VeryHigh);
    }

    /**
     * The name of the connection.
     *
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Gets the connection's database.
     *
     * @param null $table
     *
     * @throws \Exception
     *
     * @return \Dan\Database\Database|\Dan\Database\Table
     */
    public function database($table = null)
    {
        if (!is_null($table)) {
            return database($this->name)->table($table);
        }

        return database($this->name);
    }

    //region connection handlers

    /**
     * Connects to the connection.
     *
     * @return mixed
     */
    public function connect() : bool
    {
        $server = $this->config->get('server');
        $port = $this->config->get('port');

        console()->info("Connecting to {$server}:{$port}...");

        try {
            $this->socket->connect($server, $port);
        } catch (\Exception $e) {
            console()->error("Failed to connect to the IRC server: {$e->getMessage()}");

            if (!$this->reconnect()) {
                connection()->removeConnection($this);

                return false;
            }
        }

        console()->success('Connected.');
        $this->reconnectCount = 0;
        $this->login();

        return true;
    }

    /**
     * Disconnects from the connection.
     *
     * @return bool
     */
    public function disconnect() : bool
    {
        return $this->socket->disconnect();
    }

    /**
     * Reads the resource.
     *
     * @param resource $resource
     *
     * @return void
     */
    public function read($resource)
    {
        $lines = $this->socket->read();
        $slackLine = null;

        foreach ($lines as $line) {
            $line = trim($line);

            if (empty($line)) {
                continue;
            }

            if (strlen($line) == 1) {
                $this->slackLine = $line;
                continue;
            }

            if ($this->slackLine) {
                $line = $this->slackLine.$line;
                $this->slackLine = false;
            }

            console()->debug("[<magenta>{$this->name}</magenta>] >> {$line}");

            $this->handleLine($line);
        }
    }

    /**
     * Handles a line from the socket.
     *
     * @param $line
     */
    protected function handleLine($line)
    {
        $data = $this->parseLine($line);

        $from = $data['from'];
        $cmd = $data['command'];

        $data = $cmd;
        array_shift($data);

        if ($cmd[0] == 'ERROR') {
            console()->warn('Disconnected from IRC');

            $this->socket->disconnect();

            if (!$this->reconnect()) {
                connection()->removeConnection($this);
            }

            return;
        }

        $normal = ucfirst(strtolower($cmd[0]));
        $class = "Dan\\Irc\\Packets\\Packet{$normal}";

        if (!class_exists($class)) {
            console()->debug("<default>[ERROR]</default> [<red>{$this->name}</red>] <error>Unable to find packet handler for {$normal}</error>");

            return;
        }

        try {
            /** @var PacketContract $handler */
            $handler = new $class($this);
            $handler->handle($from, $data);

            unset($handler);
        } catch (\Exception $exception) {
            console()->exception($exception);
        } catch (\Error $error) {
            console()->exception($error);
        }
    }

    /**
     * Writes to the resource.
     *
     * @param $line
     *
     * @return void
     */
    public function write($line)
    {
        if (empty($line)) {
            return;
        }

        $raw = substr($line, 0, 510);

        console()->debug("[<magenta>{$this->name}</magenta>] << {$raw}");

        try {
            $this->socket->write("{$raw}\r\n");
        } catch (BrokenPipeException $e) {
            $this->reconnect();
        }
    }

    /**
     * Gets the stream resource for the connection.
     *
     * @return resource
     */
    public function getStream()
    {
        return $this->socket->getSocket();
    }

    /**
     * Reconnects to the network on failure.
     *
     * @return bool
     */
    protected function reconnect() : bool
    {
        if (!$this->quitting && $this->reconnectCount < 3) {
            $human = $this->reconnectCount + 1;

            console()->warn("Disconnected from IRC. Retry {$human} of 3 in 5 seconds..");
            sleep(5);

            $this->reconnectCount++;

            console()->info('Reconnecting to IRC..');

            return $this->connect();
        }

        return false;
    }

    //endregion

    //region irc handler

    /**
     * Sends the required USER and NICK for login.
     */
    public function login()
    {
        $nick = $this->config->get('user.nick');
        $name = $this->config->get('user.name');
        $real = $this->config->get('user.real');

        if (!empty($this->config->get('pass'))) {
            $this->send('PASS', $this->config->get('pass'));
        }

        $this->send('USER', $name, $name, '*', $real);
        $this->send('NICK', $nick);
        $this->send('WHO', $nick);
    }

    /**
     * Stops the current connection.
     *
     * @param string $reason
     *
     * @return mixed
     */
    public function quit($reason = null)
    {
        $this->quitting = true;
        $this->send('QUIT', $reason);
    }

    //endregion

    //region channel methods

    /**
     * Joins a channel.
     *
     * @param $name
     * @param string $key
     *
     * @throws \Exception
     */
    public function joinChannel($name, $key = '')
    {
        if (!in_array(substr($name, 0, 1), str_split($this->supported->get('CHANTYPES')))) {
            throw new \Exception('Invalid channel prefix '.substr($name, 0, 1));
        }

        $this->send('JOIN', $name, $key);
    }

    /**
     * Parts a channel.
     *
     * @param $name
     * @param string $reason
     */
    public function partChannel($name, $reason = 'Leaving')
    {
        if ($name instanceof Channel) {
            $name = $name->getLocation();
        }

        if (!$this->inChannel($name)) {
            return;
        }

        $name = strtolower($name);

        $this->channels->get($name)->destroy();
        $this->channels->forget($name);

        $this->send('PART', $name, $reason);
    }

    /**
     * Checks to see if the bot is in a channel.
     *
     * @param $channel
     *
     * @return bool
     */
    public function inChannel($channel) : bool
    {
        return $this->channels->has(strtolower($channel));
    }

    /**
     * Gets a channel if the bot is in it.
     *
     * @param $channel
     *
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
     *
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
     * Gets all channels on this connection.
     *
     * @return \Dan\Irc\Location\Channel[]
     */
    public function channels()
    {
        return $this->channels;
    }

    /**
     * Removes a channel.
     *
     * @param $channel
     *
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

    //endregion

    //region messaging

    /**
     * Sends a message (PRIVMSG) to the given location.
     *
     * @param $location
     * @param $message
     * @param array $styles
     *
     * @throws \Exception
     */
    public function message($location, $message, $styles = [])
    {
        if ($this->isChannel($location)) {
            if (!$this->inChannel($location)) {
                throw new \Exception("This channel doesn't exist.");
            }

            $this->triggerEvent('irc.bot.message.public', [
                'connection'    => $this,
                'user'          => $this->user,
                'channel'       => $location instanceof Channel ? $location : $this->getChannel($location),
                'message'       => $message,
            ]);
        }

        if (!config('dan.debug')) {
            console()->line("[<magenta>{$this->name}</magenta>][<cyan>{$location}</cyan>][<yellow>{$this->user->nick}</yellow>] {$message}");
        }

        $formatter = new IrcOutputFormatter(true);

        loop($styles, function($style, $name) use ($formatter) {
            $formatter->setStyle($name, new IrcOutputFormatterStyle(...$style));
        });

        $message = $formatter->format($message);

        logger()->logNetworkChannelItem($this->getName(), $location, $message, $this->user->nick);

        $this->send('PRIVMSG', $location, $message);
    }

    /**
     * Sends an ACTION to the given location.
     *
     * @param $location
     * @param $message
     * @param array $styles
     */
    public function action($location, $message, $styles = [])
    {
        $formatter = new IrcOutputFormatter(true);

        loop($styles, function($style, $name) use ($formatter) {
            $formatter->setStyle($name, new IrcOutputFormatterStyle(...$style));
        });

        $message = $formatter->format($message);

        logger()->logNetworkChannelItem($this->getName(), $location, "** {$message}", $this->user->nick);

        $this->send('PRIVMSG', $location, "\001ACTION $message\001");
    }

    /**
     * Sends a NOTICE to the given location.
     *
     * @param $location
     * @param $message
     */
    public function notice($location, $message)
    {
        $this->send('NOTICE', $location, strip_tags($message));
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

            if (is_null($add)) {
                continue;
            }

            if ($add instanceof Location) {
                $add = $add->getLocation();
            }

            if (is_array($add)) {
                $add = json_encode($add);
            }

            if (strpos($add, ' ') !== false) {
                $add = ":{$add}";
            }

            $compiled[] = $add;
        }

        $this->write(implode(' ', $compiled));
    }

    /**
     * Sends a RAW line to IRC.
     *
     * @param $line
     */
    public function raw($line)
    {
        $this->write($line);
    }

    //endregion

    //region ignore things

    /**
     * @param $user
     *
     * @return bool
     */
    public function ignore($user)
    {
        $mask = $this->getIgnoreMask($user);

        if ($this->database('ignore')->where('mask', $mask)->count()) {
            return false;
        }

        $this->database('ignore')->insert(['mask' => $mask]);

        return true;
    }

    /**
     * @param $user
     *
     * @return bool
     */
    public function unignore($user)
    {
        $mask = $this->getIgnoreMask($user);

        if (!$this->database('ignore')->where('mask', $mask)->count()) {
            return false;
        }

        $this->database('ignore')->where('mask', $mask)->delete();

        return true;
    }

    /**
     * @param $user
     *
     * @return string
     */
    protected function getIgnoreMask($user)
    {
        if ($user instanceof User) {
            return $user->mask(false);
        }

        if ($this->database('users')->where('nick', $user)->count()) {
            $dbuser = $this->database('users')->where('nick', $user)->first();

            return "*!{$dbuser->get('user', '*')}@{$dbuser->get('host', '*')}";
        }

        return makeMask($user);
    }

    //endregion
}
