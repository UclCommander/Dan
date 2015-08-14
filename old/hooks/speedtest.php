<?php

use Dan\Helpers\Web;

$regex  = "/(?:.*)http\:\/\/www\.speedtest\.net\/(?:my\-)?result\/([0-9]+)(?:\.png)?(?:.*)/";
$format = "{reset}[ {cyan}Ping: {light_cyan}{PING} {reset}| {cyan}Down: {light_cyan}{DOWNLOAD} {reset}| {cyan}Up: {light_cyan}{UPLOAD} {reset}| {orange}ISP: {ISP} {reset}| {yellow}{SERVER} {reset}| {green}{STARS} {reset}]";

hook(['regex' => $regex, 'name' => 'speedtest'], function(array $eventData, array $matches) use($format)
{
    foreach ($matches[1] as $match)
    {
        $data = Web::api('speedtest/get', ['id' => $match]);
        return parseFormat($format, $data);
    }
});