<?php namespace Plugins\Title; 


use Dan\Irc\Channel;

interface HandlerInterface {

    /**
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