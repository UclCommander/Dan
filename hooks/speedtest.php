<?php

use Dan\Helpers\Web;

$regex  = "/(?:.*)http\:\/\/www\.speedtest\.net\/(?:my\-)?result\/([0-9]+)(?:\.png)?(?:.*)/";
$format = "{reset}[ {cyan}Ping: {light_cyan}{PING} {reset}| {cyan}Down: {light_cyan}{DOWNLOAD} {reset}| {cyan}Up: {light_cyan}{UPLOAD} {reset}| {orange}ISP: {ISP} {reset}| {yellow}{SERVER} {reset}| {green}{STARS} {reset}]";

hook(['regex' => $regex], function(array $eventData, array $matches) use($format) {
    $data = Web::api('speedtest/get', ['id' => $matches[1]]);
    return parseFormat($format, $data);
});