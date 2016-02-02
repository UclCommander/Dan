<?php

namespace Dan\Database\Traits;

use Illuminate\Support\Arr;

trait Data
{
    protected $data = [];

    public function hasDataKey($key)
    {
        return Arr::has($this->data, $key);
    }

    /**
     * @param $key
     * @param $value
     */
    public function setData($key, $value)
    {
        Arr::set($this->data, $key, $value);
    }

    /**
     * @param $key
     * @param null $default
     *
     * @return mixed
     */
    public function getData($key, $default = null)
    {
        return Arr::get($this->data, $key, $default);
    }
}
