<?php

namespace Dan\Config;

use Dan\Events\Traits\EventTrigger;
use Exception;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Support\Arrayable;

class Config extends Repository implements Arrayable
{
    use EventTrigger;

    /**
     * @throws \Exception
     */
    public function load()
    {
        foreach (dan('filesystem')->glob(configPath('*.json')) as $file) {
            $name = basename($file, '.json');

            $json = json_decode(dan('filesystem')->get($file), true);

            if (!is_array($json)) {
                throw new Exception("Error loading JSON for '{$name}.json'. Please check and correct the file.");
            }

            $this->items[$name] = $json;
        }

        $this->triggerEvent('config.reload');
    }

    /**
     * @param array|string $key
     * @param null $value
     */
    public function set($key, $value = null)
    {
        $old = $this->get($key);

        parent::set($key, $value);

        $this->triggerEvent('config.set', [
            'key'   => $key,
            'value' => $value,
            'old'   => $old,
        ]);
    }

    /**
     * Gets the items as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->items;
    }
}
