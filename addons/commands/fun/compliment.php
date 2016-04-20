<?php

/**
 * Compliment Command: For when you fucked up so badly you need to calm someone down quickly!
 * Originally an insult script made by Uclcommander, edited by FM1337 to compliment instead!
 *
 * Money back offer has a 40 year waiting period for validation. There are no exceptions.
 * If you harass the money back office you'll owe us money.
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */
use Dan\Irc\Location\Channel;
use Dan\Support\Web;

command(['compliment'])
    ->helpText('Compliments someone.')
    ->handler(function (Channel $channel, $message) {
        $website = array_random([
            'http://www.chainofgood.co.uk/passiton',
            'http://toykeeper.net/programs/mad/compliments',
            'http://www.madsci.org/cgi-bin/cgiwrap/~lynn/jardin/SCG',
        ]);

        $compliment = 'You are very lovely and everyone cares about you!';

        try {
            $xpath = Web::xpath($website);

            if ($website == 'http://www.chainofgood.co.uk/passiton') {
                $class = array_random(['large', 'medium', 'small']);

                $compliment = cleanString($xpath->query("//*[@class='{$class}']")->item(0)->textContent);
            } elseif ($website == 'http://toykeeper.net/programs/mad/compliments') {
                $compliment = cleanString($xpath->query('//*[@class="blurb_title_1"]')->item(0)->textContent);
            } else {
                $compliment = cleanString($xpath->query('//h2')->item(0)->textContent);
            }
        } catch (Exception $e) {
            console()->exception($e);
        }

        if (empty($message)) {
            $channel->message($compliment);

            return;
        }

        $channel->message("{$message}: {$compliment}");
    });
