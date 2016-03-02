<?php

use Dan\Irc\Connection;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

$messageHandler = function (Channel $channel, User $user) {
    $total = $channel->getData("stats.messages.{$user->id}", 0);
    $total++;
    $channel->setData("stats.messages.{$user->id}", $total);
    $channel->save();

    $total = $user->getData('stats.messages', 0);
    $total++;
    $user->setData('stats.messages', $total);
    $user->save();
};

on('irc.message.public')->handler($messageHandler);
on('irc.action.public')->handler($messageHandler);
on('irc.bot.message.public')->handler($messageHandler);

on('irc.join')->handler(function (Channel $channel) {
    $total = $channel->getData('stats.join', 0);
    $total++;
    $channel->setData('stats.join', $total);
    $channel->save();
});

on('irc.part')->handler(function (Channel $channel) {
    $total = $channel->getData('stats.part', 0);
    $total++;
    $channel->setData('stats.part', $total);
    $channel->save();
});

on('irc.kick')->handler(function (Channel $channel) {
    $total = $channel->getData('stats.kick', 0);
    $total++;
    $channel->setData('stats.kick', $total);
    $channel->save();
});

on('irc.nick')->handler(function (Connection $connection, $user) {
    foreach ($connection->channels() as $channel) {
        if (!$channel->hasUser($user)) {
            continue;
        }

        $total = $channel->getData('stats.nick', 0);
        $total++;
        $channel->setData('stats.nick', $total);
        $channel->save();
    }
});

on('irc.topic')->handler(function (Channel $channel) {
    $total = $channel->getData('stats.topic', 0);
    $total++;
    $channel->setData('stats.topic', $total);
    $channel->save();
});

on('irc.quit')->handler(function (Connection $connection, User $user) {
    foreach ($connection->channels() as $channel) {
        if (!$channel->hasUser($user)) {
            continue;
        }

        $total = $channel->getData('stats.part', 0);
        $total++;
        $channel->setData('stats.part', $total);
        $channel->save();
    }
});
