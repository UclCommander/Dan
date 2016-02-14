<?php

namespace Dan\Web;

use Dan\Web\Traits\Parser;

class Client
{
    use Parser;

    /**
     * @var \Dan\Web\Listener
     */
    protected $listener;

    /**
     * @var
     */
    protected $client;

    /**
     * Client constructor.
     *
     * @param \Dan\Web\Listener $listener
     * @param $client
     */
    public function __construct(Listener $listener, $client)
    {
        $this->listener = $listener;
        $this->client = $client;
    }

    /**
     * Handles the client.
     */
    public function handle()
    {
        $socketData = stream_socket_recvfrom($this->client, (1024 * 32));

        if ($socketData === false) {
            $this->write(new Response('Unable to complete request', 500));
            $this->close();

            return;
        }

        $headers = $this->parseHeaders($socketData);
        $data = $this->parseUriData($headers[0]);

        console()->info("Accepted new {$data['method']} client to {$data['path']}");

        $this->formatHeaders($headers);

        $response = new Response('No routes found', 404);

        $this->write($response);
        $this->close();
    }

    /**
     * Writes a response to the client.
     *
     * @param $response
     */
    public function write(Response $response)
    {
        fwrite($this->client, $response->make());
    }

    /**
     * Closes the client connection.
     */
    public function close()
    {
        fclose($this->client);
        unset($this->client);
    }
}