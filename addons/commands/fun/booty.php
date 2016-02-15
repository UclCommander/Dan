<?php

/**
 * look at that booty, show me the booty, gimme the booty,
 * i want the booty, back up the booty, i need the booty,
 * i like the booty, oh what a booty, shakin that booty,
 * i saw the booty, i want the booty, lord want the booty,
 * bring on the booty, give up the booty, lovin the booty,
 * round booty, down for the booty, i want the booty,
 * huntin the booty, chasin the booty, casin the booty,
 * getting the booty, beautiful booty, smokin booty,
 * talk to the booty, more booty, fine booty
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */

use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

command('booty')
    ->allowPrivate()
    ->helpText('look at that booty, show me the booty, gimme the booty, i want the booty, back up the booty, i need the booty, i like the booty, oh what a booty, shakin that booty, i saw the booty, i want the booty, lord want the booty, bring on the booty, give up the booty, lovin the booty, round booty, down for the booty, i want the booty, huntin the booty, chasin the booty, casin the booty, getting the booty, beautiful booty, smokin booty, talk to the booty, more booty, fine booty')
    ->handler(function (User $user, Channel $channel = null) {
        $location = $channel ?? $user;

        $location->message('https://youtu.be/wGlBwW7f5HA');
    });
