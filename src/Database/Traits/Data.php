<?php

namespace Dan\Database\Traits;

use Illuminate\Support\Arr;

trait Data
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * @param $key
     *
     * @return bool
     */
    public function hasData($key)
    {
        return Arr::has($this->data, $key);
    }

    /**
     * @param $key
     * @param $value
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function setData($key, $value = null)
    {
        if ($value === null) {
            if(!is_array($key)) {
                throw new \Exception('Value must be an array');
            }

            $this->data = $key;

            return $this;
        }

        Arr::set($this->data, $key, $value);

        return $this;
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

    /**
     * @param $key
     * @param $value
     *
     * @return $this
     */
    public function putData($key, $value)
    {
        $current = $this->getData($key, []);
        $current[] = $value;
        $this->setData($key, $current);

        return $this;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return $this
     */
    public function forgetData($key, $value)
    {
        $current = $this->getData($key, []);

        foreach ($current as $k => $item) {
            if ($item == $value) {
                unset($current[$k]);
            }
        }

        $this->setData($key, array_values($current));

        return $this;
    }

    /**
     * @param $key
     *
     * @return $this
     */
    public function forgetDataByKey($key)
    {
        Arr::forget($this->data, $key);

        return $this;
    }
}
