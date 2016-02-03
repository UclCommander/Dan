<?php namespace Dan\Contracts;


interface UserContract
{

    /**
     * Sends a message to the user.
     *
     * @param $message
     *
     * @return mixed
     */
    public function message($message);

    /**
     * Sends a notice to the user.
     *
     * @param $message
     *
     * @return mixed
     */
    public function notice($message);

    /**
     * Gets the nick of the user.
     *
     * @return string
     */
    public function getLocation();
}