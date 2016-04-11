<?php

use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

command(['stats'])
    ->helpText('Gets stats for the current channel.')
    ->rank('vhoaq')
    ->handler(function (\Dan\Irc\Connection $connection, User $user, Channel $channel, $message) {
        if ($message == 'reset') {
            if (!$user->hasOneOf('oaq')) {
                $channel->message("You don't have permissions to reset stats.");

                return;
            }

            $channel->setData('data', []);
            $channel->message('Stats reset');

            return;
        }

        $stats = $channel->getData('stats');
        $messages = array_sum($stats['messages']);
        $users = $stats['messages'];

        if ($channel->hasUser($message)) {
            $userInfo = $connection->database('users')->where('nick', $message)->first();
            $messages = $users[$userInfo->get('id')];

            $channel->message("[ Stats ] {$message} has sent {$messages} messages");

            return;
        }

        arsort($users);

        $userId = key($users);
        $userMessages = $users[$userId];

        $userInfo = $connection->database('users')->where('id', $userId)->first();

        $user = $userInfo['nick'];

        $nick = $stats['nick'] ?? 0;
        $join = $stats['join'] ?? 0;
        $part = $stats['part'] ?? 0;
        $topic = $stats['topic'] ?? 0;

        $info = [
            "<yellow>{$messages}</yellow> <cyan>message".($messages == 1 ? '' : 's').' have been sent</cyan>',
            "<yellow>{$user}</yellow> <cyan>is the most active with</cyan> <yellow>{$userMessages}</yellow> <cyan>message".($userMessages == 1 ? '' : 's').'</cyan>',
            "<yellow>{$nick}</yellow> <cyan>nick change".($nick == 1 ? '' : 's').'</cyan>',
            "<yellow>{$join}</yellow> <cyan>join".($join == 1 ? '' : 's').'</cyan>',
            "<yellow>{$part}</yellow> <cyan>part".($part == 1 ? '' : 's').'</cyan>',
            "<yellow>{$topic}</yellow> <cyan>topic change".($topic == 1 ? '' : 's').'</cyan>',
        ];

        $channel->message('[ '.implode(' | ', $info).' ]');
    });
