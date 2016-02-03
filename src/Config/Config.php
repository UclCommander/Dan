<?php

namespace Dan\Config;

use Exception;
use Illuminate\Config\Repository;

class Config extends Repository
{
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
