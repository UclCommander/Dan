<?php

namespace Dan\Web;

use Dan\Core\Dan;
use Illuminate\Contracts\Support\Arrayable;

class Response
{
    /**
     * @var array
     */
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
     * @var string
     */
    protected $message;

    /**
     * @var int
     */
    protected $status = 200;

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * Response constructor.
     *
     * @param string $message
     * @param int $status
     */
    public function __construct($message = '', $status = 200)
    {
        $this->message = $message;
        $this->status = $status;
    }

    /**
     * @param $key
     * @param string $value
     *
     * @return \Dan\Web\Response
     *
     * @internal param $headers
     */
    public function header($key, $value = '') : Response
    {
        if (is_array($key)) {
            $this->headers = array_merge($this->headers, $key);
        } else {
            $this->headers[$key] = $value;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function make()
    {
        $message = $this->message;

        if ($message instanceof Arrayable) {
            $message = $message->toArray();
        }

        if (is_array($message)) {
            $message = json_encode($message);
            $this->header('content-type', 'application/json');
        }

        $headers = [
            'HTTP/1.1 '.$this->statusCodes[$this->status],
            'content-length'    => strlen($message),
            'content-type'      => 'text/html; charset=UTF-8',
            'date'              => date('r'),
            'expires'           => date('r', strtotime('+1 second')),
            'server'            => 'Dan '.Dan::VERSION,
            'version'           => 'HTTP/1.1',
        ];

        $compiled = [];

        foreach ($this->headers as $key => $header) {
            $headers[$key] = $header;
        }

        $headers[] = '';
        $headers[] = $message;

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
