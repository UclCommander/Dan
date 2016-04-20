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
use Dan\Support\Web;

command(['pickup'])
    ->helpText('Uses a snazy pickup line from the love doctor Dan himself to pick someone up.')
    ->handler(function (Channel $channel, $message) {
        $website = array_random([
            'http://www.pickuplinegen.com',
            'http://toykeeper.net/programs/mad/pickup',
        ]);

        $pickup = "Hey baby you're so hot, you're giving me a heat stroke!";

        try {
            $xpath = Web::xpath($website);

            if ($website == 'http://www.pickuplinegen.com') {
                $pickup = cleanString($xpath->query('//*[@id="content"]')->item(0)->textContent);
            } else {
                $pickup = cleanString($xpath->query('//*[@class="blurb_title_1"]')->item(0)->textContent);
            }
        } catch (Exception $e) {
            console()->exception($e);
        }

        if (empty($message)) {
            $channel->message($pickup);

            return;
        }

        $channel->message("{$message}: {$pickup}");
    });
