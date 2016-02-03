<?php

namespace Dan\Console;

use Dan\Contracts\UserContract;

class User implements UserContract
{
    /**
     * Sends a message to the user.
     *
     * @param $message
     * @param array $styles
     *
     * @return mixed
     */
    public function message($message, $styles = [])
    {
        console()->message($message);
    }

    /**
     * Sends a notice to the user.
     *
     * @param $message
     *
     * @return mixed
     */
    public function notice($message)
    {
        console()->notice($message);
    }

    /**
     * Gets the nick of the user.
     *
     * @return string
     */
    public function getLocation()
    {
        return 'console';
    }
}
