<?php

namespace Dan\Irc\Packets;

use Dan\Events\Traits\EventTrigger;
use Dan\Irc\Traits\Parser;

class Packet330 extends Packet
{
    use EventTrigger;
    use Parser;

    /**
     * @param array $from
     * @param array $data
     */
    public function handle(array $from, array $data)
    {
        $this->triggerEvent('irc.whois.registered', [
            'nick'  => $data[1],
            'as'    => $data[2],
        ]);
    }
}
