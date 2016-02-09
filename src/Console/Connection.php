<?php

namespace Dan\Console;

use Dan\Contracts\ConnectionContract;
use Dan\Events\Traits\EventTrigger;

class Connection implements ConnectionContract
{
    use EventTrigger;

    /**
     * @var \Symfony\Component\Console\Input\ArrayInput
     */
    public $input;

    /**
     * @var \Dan\Console\OutputStyle
     */
    public $output;

    /**
     * @var resource
     */
    protected $stream;

    /**
     * Connection constructor.
     */
    public function __construct()
    {
        $this->input = dan('input');
        $this->output = new OutputStyle($this->input, dan('output'));
    }

    /**
     * The name of the connection.
     *
     * @return string
     */
    public function getName()
    {
        return 'console';
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
     * @return bool
     */
    public function connect() : bool
    {
        if (!is_null($this->stream)) {
            return false;
        }

        $this->stream = fopen('php://stdin', 'r');
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
        // TODO: Implement disconnect() method.
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
        $message = trim(fgets($resource));

        $this->triggerEvent('console.message', [
            'connection' => $this,
            'message'    => $message,
        ]);
    }

    /**
     * Writes to the resource.
     *
     * @return void
     */
    public function write($line)
    {
        $time = date('H:i:s');
        $this->output->writeln("[{$time}] {$line}");
    }
}
