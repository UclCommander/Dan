<?php

route('index')
    ->path('/')
    ->get(function () {
        return 'Dan '.\Dan\Core\Dan::VERSION;
    });
