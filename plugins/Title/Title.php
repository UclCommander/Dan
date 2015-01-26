<?php namespace Plugins\Title;

use Dan\Contracts\PluginContract;
use Dan\Events\EventArgs;
use Dan\Plugins\Plugin;
use Plugins\Title\Handlers\ImageHandler;
use Plugins\Title\Handlers\NeweggHandler;
use Plugins\Title\Handlers\SpeedtestHandler;
use Plugins\Title\Handlers\SteamHandler;
use Plugins\Title\Handlers\WebpageHandler;
use Plugins\Title\Handlers\YoutubeHandler;

class Title extends Plugin implements PluginContract {

    /** @var HandlerInterface[] $handlers  */
    protected $handlers = [];

    /** @var array $typeMapping  */
    protected $typeMapping = [];

    /** @var array $domains  */
    protected $domains = [];

    /**
     *
     */
    public function register()
    {
        $this->listenForEvent('irc.packet.privmsg', [$this, 'getTitle'], 4);

        $this->addHandler(new ImageHandler());
        $this->addHandler(new NeweggHandler());
        $this->addHandler(new SpeedtestHandler());
        $this->addHandler(new SteamHandler());
        $this->addHandler(new WebpageHandler());
        $this->addHandler(new YoutubeHandler());
    }

    /**
     * @param \Plugins\Title\HandlerInterface $handler
     */
    public function addHandler(HandlerInterface $handler)
    {
        $name = strtolower(get_class($handler));

        $domains = $handler->getDomains();

        foreach($domains as $domain)
            $this->domains[$domain] = $name;

        $contentType = $handler->getContentType();

        foreach($contentType as $type)
            $this->typeMapping[$type] = $name;

        $this->handlers[$name] = $handler;
    }

    /**
     * Gets the title.
     *
     * @param \Dan\Events\EventArgs $eventArgs
     * @return bool|null
     */
    public function getTitle(EventArgs $eventArgs)
    {
        /** @var \Dan\Irc\Location\Channel $channel */
        $channel = $eventArgs->get('channel');
        $message = $eventArgs->get('message');

        $match = [];

        preg_match_all('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', $message, $match);

        if(count($match) == 0)
            return null;

        $matches = $match[0];

        foreach($matches as $link)
        {
            $headers = $this->parseHeaders(get_headers($link));

            if(in_array("HTTP/1.0 404 Not Found", $headers) ||
                in_array("HTTP/1.1 404 None", $headers) ||
                in_array("HTTP/1.1 404 Not Found", $headers))
            {
                $channel->sendMessage("\x03[\x035 Unable to find webpage \x03]");
                continue;
            }

            $url = parse_url($link);

            if(array_key_exists($url['host'], $this->domains))
            {
                $this->handlers[$this->domains[$url['host']]]->handleLink($channel, $headers, $link);
                continue;
            }

            if(array_key_exists('content-type', $headers))
            {
                $type = $headers['content-type']['value'];

                if(!array_key_exists($type, $this->typeMapping))
                    continue;

                $this->handlers[$this->typeMapping[$type]]->handleLink($channel, $headers, $link);
                continue;
            }
        }

        return false;
    }

    /**
     * @param $headers
     * @return array
     */
    public function parseHeaders($headers)
    {
        $list = [];

        foreach($headers as $header)
        {
            $data = explode(':', $header, 2);

            if(count($data) == 1)
            {
                $list[strtolower($data[0])] = $data[0];
                continue;
            }

            $values = explode(';', $data[1]);

            $items = [];

            foreach($values as $value)
            {
                $kv = explode('=', trim($value), 2);

                $items[(count($kv) == 1 ? 'value' : $kv[0])] = count($kv) == 1 ? trim($value) : $kv[1];
            }

            $list[strtolower($data[0])] = count($items) == 1 ? $items : $items;
        }

        return $list;
    }
}