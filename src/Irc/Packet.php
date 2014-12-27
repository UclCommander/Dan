<?php namespace Dan\Irc; 


abstract class Packet {

    public abstract function handlePacket(Connection &$connection, PacketInfo $packetInfo);
}