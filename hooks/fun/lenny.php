<?php

/**
 * Lenny command. Because booties exist.
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */

use Illuminate\Support\Collection;

hook('lenny')
    ->command(['lenny'])
    ->help('lenny faces. Optional: hugs, no, lenninati, backward(s), pumped')
    ->func(function(Collection $args) {

        switch(trim($args->get('message')))
        {
            case "hugs":
                $lenny = "(つ ͡° ͜ʖ ͡°)つ";
                break;

            case "no":
                $lenny = "( ͡°_ʖ ͡°)";
                break;

            case "lenninati":
                $lenny = "( ͡∆ ͜ʖ ͡∆)";
                break;

            case "backward":
            case "backwards":
                $lenny = "( °͡ ʖ͜ °͡  )";
                break;

            case "pumped":
                $lenny = "(ง ͠° ͟ل͜ ͡°)ง";
                break;

            default:
                $lenny = "( ͡° ͜ʖ ͡°)";
                break;
        }

        $args->get('channel')->message($lenny);
    });