<?php

namespace Dan\Irc\Traits;

use Dan\Irc\Location\Channel;

trait Helpers
{
    public function isChannel($item)
    {
        if ($item instanceof Channel) {
            return true;
        }

        $types = preg_quote($this->supported->get('CHANTYPES'));

        if ($types == null) {
            return false;
        }

        return boolval(preg_match("/[{$types}]([a-zA-Z0-9_\-\.]+)/", $item));
    }
}
