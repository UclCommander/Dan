<?php

/**
 * Lenny command. Because booties exist.
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

command('lenny')
    ->allowPrivate()
    ->helpText('lenny faces. Optional: hugs, no, lenninati, backward(s), pumped')
    ->handler(function (User $user, $message, Channel $channel = null) {
        $location = $channel ?? $user;

        switch (trim($message)) {
            case 'hugs':
                $lenny = '(つ ͡° ͜ʖ ͡°)つ';
                break;

            case 'no':
                $lenny = '( ͡°_ʖ ͡°)';
                break;

            case 'lenninati':
                $lenny = '( ͡∆ ͜ʖ ͡∆)';
                break;

            case 'backward':
            case 'backwards':
                $lenny = '( °͡ ʖ͜ °͡  )';
                break;

            case 'pumped':
                $lenny = '(ง ͠° ͟ل͜ ͡°)ง';
                break;

            default:
                $lenny = '( ͡° ͜ʖ ͡°)';
                break;
        }

        $location->message($lenny);
    });
