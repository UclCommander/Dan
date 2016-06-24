<?php

namespace Dan\Irc\Packets;

use Dan\Events\Traits\EventTrigger;
use Dan\Irc\Traits\Parser;

class Packet318 extends Packet
{
    use EventTrigger;
    use Parser;

    /**
     * @param array $from
     * @param array $data
     */
    public function handle(array $from, array $data)
    {
        $this->triggerEvent('irc.whois.end', [
            'nick'  => $data[1],
        ]);
    }
}
