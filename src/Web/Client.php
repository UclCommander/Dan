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

        if (is_array($headers['data'])) {
            $data['query'] = array_merge($data['query'] ?? [], $headers['data']);
        }

        $response = $this->gotoRoute($data, $headers);

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

    /**
     * Finds and runs the route if it exists.
     *
     * @param $data
     * @param $headers
     *
     * @return \Dan\Web\Response
     */
    protected function gotoRoute($data, $headers) : Response
    {
        foreach ($this->listener->routes() as $name => $route) {
            /** @var Route $route */
            if ($route->getPath() != $data['path']) {
                continue;
            }

            if ($route->getMethod() != $data['method']) {
                continue;
            }

            return $this->handleRoute($route, $headers, $data);
        }

        return new Response('Route not found', 404);
    }

    /**
     * Handles running the route.
     *
     * @param \Dan\Web\Route $route
     * @param $headers
     *
     * @return \Dan\Web\Response
     */
    protected function handleRoute(Route $route, $headers, $data) : Response
    {
        $handler = $route->getHandler();

        if (!($handler instanceof \Closure)) {
            $handler = [$handler, 'run'];
        }

        $response = dan()->call($handler, [
            'request' => new Request($data, $headers),
        ]);

        if ($response instanceof Response) {
            return $response;
        }

        return new Response($response);
    }
}
