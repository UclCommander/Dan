<?php namespace Dan\Irc;


interface PacketInterface {

    public function run(Connection &$connection, array $data, User $user);
}
 