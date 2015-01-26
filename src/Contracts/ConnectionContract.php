<?php namespace Dan\Contracts;


interface ConnectionContract {

    public function run();

    public function sendNick($nick);
    public function sendRaw($data);
    public function sendMessage($location, ...$message);
    public function sendNotice($location, ...$message);

    public function joinChannel($channel, $password = null);
    public function addChannel($name);
    public function getChannel($name);
}
 