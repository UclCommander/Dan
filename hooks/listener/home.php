<?php

use Illuminate\Support\Collection;

hook('home')
    ->http()
    ->get('/')
    ->func(function(Collection $args) {
        return response('Dan ' . \Dan\Core\Dan::VERSION);
    });