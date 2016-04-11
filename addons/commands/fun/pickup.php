<?php

/**
 * Professional Pick-up lines from the Love Doctor himself Dan!
 *
 *
 *(Please note, we are not reponsible if you get the shit beat out of you)
 *(I am so sorry that I even created this)
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;
use Dan\Support\Web;

command(['pickup'])
    ->helpText('Uses a snazy pickup lines from the love doctor Dan himself to pick someone up.')
    ->handler(function (\Dan\Irc\Connection $connection, Channel $channel, User $user, $message) {
        $websites = ['http://www.pickuplinegen.com', 'http://toykeeper.net/programs/mad/pickup'];
        $website = array_random($websites);
        $pickup = Web::xpath($website);
        if ($website == 'http://www.pickuplinegen.com') {
            $pickup = cleanString($pickup->query('//*[@id="content"]')->item(0)->textContent);
        } elseif ($website == 'http://toykeeper.net/programs/mad/pickup') {
            $pickup = cleanString($pickup->query('//*[@class="blurb_title_1"]')->item(0)->textContent);
        }
/* In case the chosen website fails to load (I will admit this is not my finest pickup line)*/
        else {
            $pickup = "Hey baby you're so hot, you're giving me a heat stroke!";
        }

        if (empty($message)) {
            $channel->message($pickup);

            return;
        }

        if (!$channel->hasUser($message)) {
            $channel->message("I can't pick up someone who isn't here!");

            return;
        }

        $pickingup = $channel->getUser($message)->nick;

        if ($message == $connection->user->nick) {
            $pickingup = $user->nick;
        }

        $channel->message("{$pickingup}: {$pickup}");
    });
