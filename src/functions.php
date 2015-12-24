<?php


use Dan\Web\Response;

if (!function_exists('response')) {

    /**
     * @param null $message
     * @param int $code
     * @return \Dan\Web\Response
     */
    function response($message = null, $code = 200)
    {
        return new Response($message, $code);
    }
}

if (!function_exists('parse_headers')) {

    /**
     * Parses http headers
     *
     * @param $raw_headers
     * @return array
     */
    function parse_headers($raw_headers)
    {
        $headers = [];
        $key = '';

        $data = false;

        foreach (explode("\n", $raw_headers) as $i => $h) {

            if(empty(trim($h))) {
                $data = true;
                continue;
            }

            if($data) {
                $headers['data'] = trim(($headers['data'] ?? "\n") . $h);
                continue;
            }

            $h = explode(':', $h, 2);

            if (isset($h[1])) {
                if (!isset($headers[strtolower($h[0])])) {
                    $headers[strtolower($h[0])] = trim($h[1]);
                } elseif (is_array($headers[strtolower($h[0])])) {
                    $headers[strtolower($h[0])] = array_merge($headers[strtolower($h[0])], [trim($h[1])]);
                } else {
                    $headers[strtolower($h[0])] = array_merge([$headers[strtolower($h[0])]], [trim($h[1])]);
                }

                $key = strtolower($h[0]);
            } else {
                if (substr(strtolower($h[0]), 0, 1) == "\t") // [+]
                {
                    $headers[$key] .= "\r\n\t".trim(strtolower($h[0]));
                } elseif (!$key) {
                    $headers[0] = trim(strtolower($h[0]));
                }

                trim(strtolower($h[0]));
            }
        }

        return $headers;
    }
}

if (!function_exists("shortLink")) {

    /**
     * Creates a short link with the configured api
     *
     * @param $link
     * @return mixed
     */
    function shortLink($link)
    {
        if(!config('dan.use_short_links')) {
            return $link;
        }

        $class = config('dan.short_link_api');

        /** @var \Dan\Contracts\ShortLinkContract $creator */
        $creator = new $class();

        return $creator->create($link);
    }
}