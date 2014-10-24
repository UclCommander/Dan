<?php namespace Dan\Irc;


class User
{
    private $data = [];

    public function getName() { return $this->data[1]; }
    public function getNick() { return $this->data[0]; }
    public function getHost() { return $this->data[2]; }

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function sendMessage($message)
    {
        //App::base()->connection->sendMessage($this->getNick(), $message);
    }

    public function sendNotice($message)
    {
        //App::base()->connection->sendNotice($this->getNick(), $message);
    }
}


 