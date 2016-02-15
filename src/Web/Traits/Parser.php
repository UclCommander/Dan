<?php

namespace Dan\Web\Traits;

trait Parser
{
    /**
     * Parses http headers.
     *
     * @param $raw
     *
     * @return array
     */
    public function parseHeaders($raw)
    {
        $headers = [];
        $key = '';
        $data = false;

        foreach (explode("\n", $raw) as $i => $h) {
            if (empty(trim($h))) {
                $data = true;
                continue;
            }

            if ($data) {
                $headers['data'] = trim(($headers['data'] ?? "\n").$h);
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
                if (substr(strtolower($h[0]), 0, 1) == "\t") {
                    $headers[$key] .= "\r\n\t".trim(strtolower($h[0]));
                } elseif (!$key) {
                    $headers[0] = trim(strtolower($h[0]));
                }

                trim(strtolower($h[0]));
            }
        }

        return $headers;
    }

    /**
     * Parses URI header data.
     *
     * @param $header
     *
     * @return array
     */
    public function parseUriData($header)
    {
        $data = explode(' ', $header);
        $method = strtolower($data[0]);
        $path = parse_url("http://127.0.0.1{$data[1]}");

        if (isset($path['query'])) {
            $path['query'] = $this->parseQuery($path['query']);
        }

        return array_merge(['method' => $method], $path);
    }

    /**
     * Parses a query string.
     *
     * @param $query
     *
     * @return mixed
     */
    public function parseQuery($query)
    {
        parse_str($query, $data);

        return $data;
    }

    /**
     * Formats the headers like a boss.
     *
     * @param $headers
     */
    public function formatHeaders(&$headers)
    {
        if (isset($headers['content-type'])) {
            switch ($headers['content-type']) {
                case 'application/x-www-form-urlencoded':
                    $headers['data'] = $this->parseQuery($headers['data']);
                    break;
                case 'application/json':
                    $headers['data'] = json_decode($headers['data'], true);
                    break;
            }
        }
    }
}
