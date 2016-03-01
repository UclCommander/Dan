<?php

namespace Dan\Support;

class Url
{
    /**
     * Gets all headers from a URL.
     *
     * @param $url
     *
     * @return array
     */
    public static function getHeaders($url)
    {
        $new = [];
        $headers = get_headers($url, true);

        foreach ($headers as $key => $value) {
            $new[strtolower($key)] = $value;
        }

        return $new;
    }

    /**
     * Gets the address that the provided URL redirects to,
     * or FALSE if there's no redirect.
     *
     * @param string $url
     *
     * @return string
     */
    public static function getRedirectUrl($url)
    {
        $redirect_url = null;
        $url_parts = @parse_url($url);

        if (!$url_parts) {
            return false;
        }

        if (!isset($url_parts['host'])) {
            return false;
        }

        if (!isset($url_parts['path'])) {
            $url_parts['path'] = '/';
        }

        $sock = fsockopen(
            $url_parts['host'],
            (isset($url_parts['port']) ? (int) $url_parts['port'] : 80),
            $errno,
            $errstr,
            30
        );

        if (!$sock) {
            return false;
        }

        $request = 'HEAD '.$url_parts['path'].(isset($url_parts['query']) ? '?'.$url_parts['query'] : '')." HTTP/1.1\r\n";
        $request .= 'Host: '.$url_parts['host']."\r\n";
        $request .= "Connection: Close\r\n\r\n";

        fwrite($sock, $request);

        $response = '';

        while (!feof($sock)) {
            $response .= fread($sock, 8192);
        }

        fclose($sock);

        if (preg_match('/^Location: (.+?)$/m', $response, $matches)) {
            if (substr($matches[1], 0, 1) == '/') {
                return $url_parts['scheme'].'://'.$url_parts['host'].trim($matches[1]);
            }

            return trim($matches[1]);
        }

        return false;
    }

    /**
     * Follows and collects all redirects, in order, for the given URL.
     *
     * @param string $url
     *
     * @return array
     */
    public static function getAllRedirects($url)
    {
        $redirects = [];

        while ($newurl = static::getRedirectUrl($url)) {
            if (in_array($newurl, $redirects)) {
                break;
            }

            $redirects[] = $newurl;
            $url = $newurl;
        }

        return $redirects;
    }

    /**
     * Gets the address that the URL ultimately leads to.
     * Returns $url itself if it isn't a redirect.
     *
     * @param string $url
     *
     * @return string
     */
    public static function getFinalUrl($url)
    {
        $redirects = static::getAllRedirects($url);

        if (count($redirects) > 0) {
            return array_pop($redirects);
        }

        return $url;
    }
}
