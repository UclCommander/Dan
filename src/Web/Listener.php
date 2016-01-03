<?php

namespace Dan\Web;

use Dan\Contracts\SocketContract;
use Dan\Core\Dan;
use Dan\Hooks\HookManager;
use Illuminate\Support\Collection;

class Listener implements SocketContract
{
    protected $statusCodes = [
        200 => '200 OK',
        201 => '201 Created',
        202 => '202 Accepted',
        203 => '203 Non-Authoritative Information',
        204 => '204 No Content',
        400 => '400 Bad Request',
        401 => '401 Unauthorized',
        403 => '403 Forbidden',
        404 => '404 Not Found',
        405 => '405 Method Not Allowed',
        500 => '500 Internal Server Error',
        501 => '501 Not Implemented',
        502 => '502 Bad Gateway',
        503 => '503 Service Unavailable',
    ];

    /**
     * @var \Illuminate\Support\Collection
     */
    public $config;

    /**
     * @var resource
     */
    protected $socket;

    /**
     * @var resource
     */
    protected $client;

    /**
     * Listener constructor.
     */
    public function __construct()
    {
        $this->config = new Collection([
            'command_prefix'   => '/',
        ]);

        info('Starting socket listener on tcp://'.config('web.host').':'.config('web.port'));
        $this->socket = stream_socket_server('tcp://'.config('web.host').':'.config('web.port'), $errno, $errstr);

        if ($this->socket === false) {
            throw new \Exception($errstr);
        }

        stream_set_blocking($this->socket, 0);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'listener';
    }

    /**
     * @return resource
     */
    public function getStream()
    {
        return $this->socket;
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
        if (!is_null($this->client)) {
            fclose($this->client);
        }

        fclose($this->socket);

        unset($this->client);
        unset($this->socket);
    }

    /**
     * Handles the socket event.
     *
     * @param $resource
     */
    public function handle($resource)
    {
        $this->client = stream_socket_accept($resource);

        if ($this->client === false) {
            return;
        }

        $socketData = stream_socket_recvfrom($this->client, (1024 * 32));

        if ($socketData === false) {
            $this->write(response('Unable to complete request', 500));
            $this->close();

            return;
        }

        $headers = parse_headers($socketData);
        $data = $this->parseUriData($headers[0]);

        info("Accepted new {$data['method']} client to {$data['path']}");

        if (isset($headers['content-type'])) {
            switch ($headers['content-type']) {
                case 'application/x-www-form-urlencoded':
                    $headers['data'] = $this->parseQuery($headers['data']);
                    break;
                case 'application/json':
                    $headers['data'] = json_decode($headers['data'], true);
                    break;
            }
        }

        try {
            $return = HookManager::data($data)->callHttpHooks(array_merge($headers, $data));
        } catch (\Error $error) {
            $return = response($error->getMessage().' in '.$error->getFile().':'.$error->getLine(), 500);
        } catch (\Exception $exception) {
            $return = response($exception->getMessage().' in '.$exception->getFile().':'.$exception->getLine(), 500);
        }

        $this->write($return);
        $this->close();
    }

    /**
     * @param $response
     */
    protected function write($response)
    {
        fwrite($this->client, $this->makeResponse($response));
    }

    /**
     *
     */
    protected function close()
    {
        fclose($this->client);
        unset($this->client);
    }

    /**
     * Parses URI header data.
     *
     * @param $header
     *
     * @return array
     */
    protected function parseUriData($header)
    {
        $data = explode(' ', $header);
        $method = strtolower($data[0]);
        $path = parse_url("http://127.0.0.1{$data[1]}");

        if (isset($path['query'])) {
            $path['query'] = $this->parseQuery($path['query']);
        }

        return array_merge(['method' => $method], $path);
    }

    /**
     * Parses a query string.
     *
     * @param $query
     *
     * @return mixed
     */
    protected function parseQuery($query)
    {
        parse_str($query, $data);

        return $data;
    }

    /**
     * @param $data
     *
     * @return string
     */
    protected function makeResponse($data)
    {
        $content = null;

        $headers = [
            'HTTP/1.1 200 OK',
            'content-length'    => 0,
            'content-type'      => 'text/html; charset=UTF-8',
            'date'              => date('r'),
            'expires'           => date('r', strtotime('+1 second')),
            'server'            => 'Dan '.Dan::VERSION,
            'version'           => 'HTTP/1.1',
        ];

        if ($data instanceof Response) {
            $headers[0] = 'HTTP/1.1 '.$this->statusCodes[$data->getCode()];
            $headers['content-length'] = strlen($data->getMessage());
            $headers[] = '';
            $headers[] = $data->getMessage();
        }

        $compiled = [];

        foreach ($headers as $key => $header) {
            if (is_int($key)) {
                $compiled[] = $header;
            } else {
                $compiled[] = "{$key}: {$header}";
            }
        }

        return implode("\r\n", $compiled);
    }
}
