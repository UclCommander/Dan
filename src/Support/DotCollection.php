<?php

namespace Dan\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class DotCollection extends Collection
{
    /**
     * Gets an item using dot notation.
     *
     * @param mixed $key
     * @param null  $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (Arr::has($this->items, $key)) {
            return Arr::get($this->items, $key);
        }

        return $default;
    }

    /**
     * Gets an item using dot notation.
     *
     * @param mixed $key
     * @param mixed $value
     *
     * @return mixed
     */
    public function put($key, $value)
    {
        Arr::set($this->items, $key, $value);
    }

    /**
     * Puts a value only if it doesn't exist with dot notation.
     *
     * @param $key
     * @param $value
     *
     * @return bool|null
     */
    public function putIfNull($key, $value)
    {
        if (Arr::has($this->items, $key)) {
            return true;
        }

        Arr::set($this->items, $key, $value);

        return true;
    }

    /**
     * @param $key
     * @param $new
     *
     * @return bool
     */
    public function renameKey($key, $new)
    {
        if (!$this->offsetExists($key)) {
            return false;
        }

        if ($this->offsetExists($new)) {
            return false;
        }

        $old = Arr::get($this->items, $key);
        Arr::forget($this->items, $key);
        Arr::set($this->items, $new, $old);

        return true;
    }
}
