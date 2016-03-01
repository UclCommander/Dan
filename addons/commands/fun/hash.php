<?php

/**
 * Runs hash() on the given string.
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

command(['hash'])
    ->allowPrivate()
    ->helpText([
        'hash <algo> <text> - Hashes <text> with <algo>',
        'See http://skycld.co/php-hash and http://skycld.co/php-algos for more information.',
        'bcrypt is also available as a type.',
    ])
    ->handler(function (User $user, $message, Channel $channel = null) {
        $location = $channel ?? $user;
        $data = explode(' ', $message);

        if (count($data) != 2) {
            if (in_array($data[0], array_merge(hash_algos(), ['bcrypt']))) {
                $location->message('Please specify text to hash.');

                return;
            }
            $location->message('Please specify a valid algorithm.');

            return;
        }

        if ($data[0] == 'bcrypt') {
            $location->message(password_hash($data[1], PASSWORD_BCRYPT));

            return;
        }

        if (in_array($data[0], hash_algos())) {
            $location->message(hash($data[0], $data[1]));

            return;
        }

        $location->message('Please specify a valid algorithm.');
    });
