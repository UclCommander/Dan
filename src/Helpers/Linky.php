<?php namespace Dan\Helpers; 


class Linky {

    public static function fetchLink($link, $hash = '', $all = false)
    {
        $opts = ['http' => ['ignore_errors' => true]];

        $context    = stream_context_create($opts);
        $link       = urlencode(urlencode($link));
        $req        = @file_get_contents("http://skycld.co/api/v1/link/create?link={$link}&hash={$hash}", false, $context);

        if($req == null)
            return $all ? false : $link;

        $json = json_decode($req, true);

        if($all)
            return $json;

        return isset($json['error']) ? $link : $json['url'];
    }
}