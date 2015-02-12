<?php namespace Dan\Irc;


use Dan\Irc\Helpers\Color;
use Dan\Irc\Location\Location;

class MessageBuilder {

    protected $messages = [];

    protected $overflowMessage = null;

    protected $totalLength = 0;
    protected $requiredLength = 0;

    protected $rfcLength = 470;

    /**
     * Adds a message that can be cut up.
     *
     * @param string $message
     * @param bool $color
     */
    public function message($message, $color = true)
    {
        if($color)
            $message = Color::parse($message);

        $length             = strlen($message);
        $this->totalLength  += $length;
        $this->messages[]   = [$message, false, $length];
    }

    /**
     * Adds a message that MUST be left intact.
     *
     * @param string $message
     * @param bool $color
     */
    public function required($message, $color = true)
    {
        if($color)
            $message = Color::parse($message);

        $length                 = strlen($message);
        $this->totalLength      += $length;
        $this->requiredLength   += $length;
        $this->messages[]       = [$message, true, $length];
    }

    /**
     * Message to send if the original is too long.
     *
     * @param $message
     */
    public function overflowMessage($message)
    {
        $this->overflowMessage = Color::parse($message);
    }

    /**
     * Parse the message.
     *
     * @param \Dan\Irc\Location\Location $location
     */
    public function parse(Location $location)
    {
        $compiled   = '';
        $rawLength  = strlen("PRIVMSG {$location->getName()} :\n\r") + $this->requiredLength + 4;

        foreach($this->messages as $msg)
        {
            $message    = $msg[0];
            $required   = $msg[1];
            $length     = $msg[2];

            if($required)
            {
                $compiled .= $message;
                continue;
            }

            $overflow   = $this->rfcLength - $rawLength;
            $compiled  .= ($length > $overflow) ? trim(substr($message, 0, $overflow)) . '...' : $message;
        }

        $location->sendMessage($compiled);

        if($this->overflowMessage && $this->totalLength > $this->rfcLength)
            $location->sendMessage($this->overflowMessage);
    }
}