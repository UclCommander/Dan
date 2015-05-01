<?php namespace Dan\Contracts;


interface PacketContract {

    public function handle($from, $data);
}