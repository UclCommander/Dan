<?php namespace Dan\Irc\Contracts; 


interface ConnectionContract {

    public function init();
    public function run();

    public function setNick($nick);

    public function sendRaw($data);
    public function sendMessage($location, ...$message);
    public function sendNotice($location, ...$message);

    public function joinChannel($channel, $password = null);

    /**
     * Add a channel to the list if it doesn't exist.
     *
     * @param $name
     * @return \Dan\Irc\Channel
     */
    public function addChannel($name);

    /**
     * Gets a channel.
     *
     * @param $name
     * @return \Dan\Irc\Channel|null
     */
    public function getChannel($name);
}
 