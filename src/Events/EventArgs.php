<?php namespace Dan\Events; 


use Dan\Irc\PacketInfo;
use Illuminate\Support\Collection;

class EventArgs extends Collection
{
    /**
     * @param array|PacketInfo $data
     */
    public function __construct($data)
    {
        if($data instanceof PacketInfo)
            $data = $data->toArray();

        parent::__construct($data);
    }
}