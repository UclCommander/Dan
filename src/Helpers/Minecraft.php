<?php namespace Dan\Helpers; 


use Dan\Core\Config;
use Dan\Irc\Location\User;

class Minecraft {

    public static function parse(&$mcuser, User $user, &$message)
    {
        $matches = [];

        $search = Config::get('dan.minecraft.regex.message');

        preg_match($search, $message, $matches);

        if(count($matches) == 0)
            return;

        $mcuser     = new User($matches[1], $user->getNick(), 'minecraft.server');
        $message    = $matches[2];
    }
}