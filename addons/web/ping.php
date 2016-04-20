<?php

route('index')
    ->path('/ping')
    ->get(function () {
        events()->fire('system.ping');
    });
