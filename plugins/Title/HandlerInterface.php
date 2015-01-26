<?php namespace Plugins\Title; 


use Dan\Irc\Location\Channel;

interface HandlerInterface {

    /**
     * @param \Dan\Irc\Location\Channel $channel
     * @param array $headers
     * @param string $link
     * @return string
     */
    public function handleLink(Channel $channel, array $headers, $link);

    /**
     * @return array
     */
    public function getContentType();

    /**
     * @return array
     */
    public function getDomains();
}