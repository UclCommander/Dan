<?php

/**
 * Insult Command. Guaranteed to insult someone or your money back.
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

command(['insult'])
    ->helpText('Insults someone.')
    ->handler(function (\Dan\Irc\Connection $connection, Channel $channel, User $user, $message) {
        $insult = Web::xpath('http://www.insultgenerator.org/');
        $insult = trim($insult->query('//*[@class="wrap"]')->item(0)->textContent);

        if (empty($message)) {
            $channel->message($insult);

            return;
        }

        if (!$channel->hasUser($message)) {
            $channel->message("I can't insult someone who isn't here!");

            return;
        }

        $insulting = $channel->getUser($message)->nick;

        if ($message == $connection->user->nick) {
            $insulting = $user->nick;
        }

        $channel->message("{$insulting}: {$insult}");
    });
