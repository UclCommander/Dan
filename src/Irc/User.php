<?php namespace Dan\Irc;


use Dan\Core\Dan;

class User
{
    private $data = [];

    public function getName() { return $this->data[1]; }
    public function getNick() { return $this->data[0]; }
    public function getHost() { return $this->data[2]; }
    public function getRank() { return @$this->data[3]; }

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Sends a message to the user.
     *
     * @param $message
     */
    public function sendMessage($message)
    {
        Dan::app('irc')->sendMessage($this->getNick(), $message);
    }

    /**
     * Sends a notice to the user.
     *
     * @param $message
     */
    public function sendNotice($message)
    {
        Dan::app('irc')->sendNotice($this->getNick(), $message);
    }
}


 