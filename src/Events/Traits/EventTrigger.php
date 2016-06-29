<?php

namespace Dan\Events\Traits;

trait EventTrigger
{
    /**
     * @param $name
     * @param array $args
     *
     * @return array
     */
    public function triggerEvent($name, $args = [])
    {
        if (defined('SETUP')) {
            return false;
        }

        try {
            return events()->fire($name, $args);
        } catch (\ReflectionException $e) {
            return false;
        }
    }
}
