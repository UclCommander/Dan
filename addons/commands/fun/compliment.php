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
use Dan\Irc\Location\User;
use Dan\Support\Web;

command(['compliment'])
    ->helpText('Compliments someone.')
    ->handler(function (\Dan\Irc\Connection $connection, Channel $channel, User $user, $message) {
        $websites = ['http://toykeeper.net/programs/mad/compliments', 'http://www.chainofgood.co.uk/passiton', 'http://www.madsci.org/cgi-bin/cgiwrap/~lynn/jardin/SCG'];
        $website = array_random($websites);
        $classes = ['large', 'medium', 'small'];
        $class = array_random($classes);
        $compliment = Web::xpath($website);
        if ($website == 'http://www.chainofgood.co.uk/passiton') {
            $compliment = cleanString($compliment->query("//*[@class='{$class}']")->item(0)->textContent);
        } elseif ($website == 'http://www.madsci.org/cgi-bin/cgiwrap/~lynn/jardin/SCG') {
            $compliment = cleanString($compliment->query('//h2')->item(0)->textContent);
        } elseif ($website == 'http://toykeeper.net/programs/mad/compliments') {
            $compliment = cleanString($compliment->query('//*[@class="blurb_title_1"]')->item(0)->textContent);
        }
/* In case the chosen website fails top load */
        else {
            $compliment = 'You are very lovely and everyone cares about you!';
        }

        if (empty($message)) {
            $channel->message($compliment);
            return;
        }

        if (!$channel->hasUser($message)) {
            $channel->message("I can't compliment someone who isn't here!");
            return;
        }

        $complimenting = $channel->getUser($message)->nick;

        if ($message == $connection->user->nick) {
            $complimenting = $user->nick;
        }

        $channel->message("{$complimenting}: {$compliment}");
    });
