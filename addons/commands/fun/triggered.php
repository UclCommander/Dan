<?php

use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

command(['triggered'])
    ->allowPrivate()
    ->helpText('I HaS bEn TRIggERED FRoM ReADIng tHIS TExT OF HeLPerNEsS')
    ->handler(function (User $user, $message, Channel $channel = null) {
        $location = $channel ?? $user;

        $location->message('http://i0.kym-cdn.com/photos/images/newsfeed/001/053/925/9d8.gif');
    });
