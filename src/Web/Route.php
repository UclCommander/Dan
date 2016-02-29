<?php

namespace Dan\Web;

class Route
{
    /**
     * @var string
     */
    protected $method;

    /**
     * @var callable
     */
    protected $handler;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var
     */
    protected $name;

    /**
     * Route constructor.
     *
     * @param $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @param $path
     *
     * @return \Dan\Web\Route
     */
    public function path($path) : Route
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @param array $data
     *
     * @return \Dan\Web\Route
     */
    public function config(array $data) : Route
    {
        if (!file_exists(configPath(str_replace('.', '_', $this->name).'.json'))) {
            filesystem()->put(configPath(str_replace('.', '_', $this->name).'.json'), json_encode($data, JSON_PRETTY_PRINT));
        }

        return $this;
    }

    /**
     * @param $handler
     */
    public function post($handler)
    {
        $this->add('post', $handler);
    }

    /**
     * @param $handler
     */
    public function get($handler)
    {
        $this->add('get', $handler);
    }

    /**
     * @param $method
     * @param $handler
     */
    protected function add($method, $handler)
    {
        $this->method = $method;
        $this->handler = $handler;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return callable
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
}
