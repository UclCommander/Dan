<?php namespace Dan\Helpers; 


class Web {

    protected static $api = "http://api.uclcommander.net/";

    public static function curl($type, $url, $params = [], $headers = [])
    {
        $headers = array_merge(['X-Requested-With: XMLHttpRequest'], (array)$headers);

        $curl = curl_init();

        if($type == 'get')
        {
            $url = $url . "?" . http_build_query($params);
        }
        else if ($type == 'post')
        {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($curl);
        curl_close($curl);

        return $result;
    }

    /**
     * @param $uri
     * @param array $params
     * @param array $headers
     * @return mixed
     */
    public static function get($uri, $params = [], $headers = [])
    {
        return static::curl('get', $uri, $params, $headers);
    }

    /**
     * @param $uri
     * @param array $params
     * @param array $headers
     * @return mixed
     */
    public static function post($uri, $params = [], $headers = [])
    {
        return static::curl('post', $uri, $params, $headers);
    }

    /**
     * @param $uri
     * @param array $params
     * @param array $headers
     * @return mixed
     */
    public static function json($uri, $params = [], $headers = [])
    {
        $headers = array_merge(['X-Requested-With: XMLHttpRequest'], (array)$headers);

        return json_decode(static::get($uri, $params, $headers), true);
    }

    /**
     * @param $endpoint
     * @param array $data
     * @return mixed
     */
    public static function api($endpoint, $data = [])
    {
        return static::json(static::$api.$endpoint, $data, ['X-Service: Dan']);
    }

    /**
     * Extract all links from a message.
     *
     * @param $message
     * @return array
     */
    public static function extractLinks($message)
    {
        $match = [];

        preg_match_all('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', $message, $match);

        if(count($match[0]) == 0)
            return [];

       return $match[0];
    }
}