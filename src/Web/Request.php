<?php

namespace Dan\Web;

use Dan\Support\DotCollection;
use Illuminate\Contracts\Support\Arrayable;

class Request implements Arrayable
{
    /**
     * @var DotCollection
     */
    protected $data;

    /**
     * @var
     */
    protected $headers;

    /**
     * Request constructor.
     *
     * @param $data
     * @param $headers
     */
    public function __construct($data, $headers)
    {
        $this->data = dotcollect($data);
        $this->headers = $headers;
    }

    /**
     * @param $header
     * @param string $default
     *
     * @return string
     */
    public function header($header, $default = '')
    {
        if (!isset($this->headers[$header])) {
            return $default;
        }

        return $this->headers[$header];
    }

    /**
     * @return mixed
     */
    public function method()
    {
        return $this->data['method'];
    }

    /**
     * @return mixed
     */
    public function scheme()
    {
        return $this->data['scheme'];
    }

    /**
     * @return mixed
     */
    public function host()
    {
        return $this->data['host'];
    }

    public function path()
    {
        return $this->data['path'];
    }

    /**
     * @param $key
     * @param string $default
     *
     * @return string
     */
    public function get($key, $default = '')
    {
        return $this->data->get("query.{$key}", $default);
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'data'    => $this->data,
            'headers' => $this->headers,
        ];
    }
}
