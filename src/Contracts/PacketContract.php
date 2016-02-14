<?php

namespace Dan\Contracts;

interface PacketContract
{
    /**
     * @param array $from
     * @param array $data
     */
    public function handle(array $from, array $data);
}
