<?php namespace Dan\Irc; 


use Dan\Core\Dan;

class Sendable {

    /** @var string  */
    protected $location = '';

    /** @var string  */
    protected $modes = '';

    /**
     * Checks to see if a mode exists.
     *
     * @param $m
     * @return bool
     */
    public function hasMode($m)
    {
        return in_array($m, $this->modes);
    }

    /**
     * Sets a mode to the object.
     *
     * @param $m
     * @return bool
     */
    public function setMode($m)
    {
        $data   = str_split($m);
        $add    = true;

        for($i = 0; $i < count($data); $i++)
        {
            if($i == '+' || $add == '-')
            {
                $add = ($add == '+');
                continue;
            }

            if(!$add)
            {
                unset($this->modes[$data[$i]]);
                continue;
            }

            $this->modes[$data[$i]] = $data[$i];
        }
    }

    /**
     * Sends a message.
     *
     * @param $message
     */
    public function sendMessage($message)
    {
        Dan::app('connection')->sendMessage($this->location, $message);
    }

    /**
     * Sends a notice.
     *
     * @param $message
     */
    public function sendNotice($message)
    {
        Dan::app('connection')->sendNotice($this->location, $message);
    }
}