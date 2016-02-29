<?php

namespace Dan\Web;

use Dan\Contracts\ConnectionContract;
use Dan\Events\Event;
use Illuminate\Support\Collection;

class Listener implements ConnectionContract
{
    /**
     * @var
     */
    protected $stream;

    /**
     * @var
     */
    protected $routes;

    /**
     * @var array
     */
    protected $config;

    /**
     * Listener constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->routes = new Collection();
        $this->config = $config;

        events()->subscribe('addons.load', function () {
            $this->routes = new Collection();
        }, Event::VeryHigh);
    }

    /**
     * The name of the connection.
     *
     * @return string
     */
    public function getName()
    {
        return 'listener';
    }

    /**
     * Gets the stream resource for the connection.
     *
     * @return resource
     */
    public function getStream()
    {
        return $this->stream;
    }

    /**
     * Connects to the connection.
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function connect() : bool
    {
        console()->info('Starting socket listener on tcp://'.$this->config['host'].':'.$this->config['port']);
        $this->stream = stream_socket_server('tcp://'.$this->config['host'].':'.$this->config['port'], $errno, $errstr);

        if ($this->stream === false) {
            throw new \Exception($errstr);
        }

        stream_set_blocking($this->stream, 0);

        return true;
    }

    /**
     * Disconnects from the connection.
     *
     * @return bool
     */
    public function disconnect() : bool
    {
        fclose($this->stream);
        return true;
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
        $client = stream_socket_accept($resource);

        if ($client === false) {
            return;
        }

        $client = new Client($this, $client);
        $client->handle();
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
        // TODO: Implement write() method.
    }

    /**
     * Registers a route to the listener.
     *
     * @param $name
     *
     * @return \Dan\Web\Route
     */
    public function registerRoute($name) : Route
    {
        console()->info("Loading route {$name}");
        $route = new Route($name);
        $this->routes->put($name, $route);

        return $this->routes->get($name);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function routes() : Collection
    {
        return $this->routes;
    }
}
