<?php


use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;
use Illuminate\Support\Collection;

hook('stats')
    ->command(['stats'])
    ->help("Gets stats for the current or given channel")
    ->func(function(Collection $args) {
        /** @var Channel $channel */
        $channel = $args->get('channel');
        $message = $args->get('message');

        if($message && isChannel($message))
            $channel = $message;

        $data = database()->table('channels')->where('name', $channel)->first();

        $stats = $data['info']['stats'];

        $users = $stats['users'];

        sort($users);

        $user = array_keys($stats['users'])[0];
        $messages = $stats['users'][$user];

        $nick   = $stats['nick'] ?? 0;
        $join   = $stats['join'] ?? 0;
        $part   = $stats['part'] ?? 0;
        $topic  = $stats['topic'] ?? 0;

        $info = [
            "<yellow>{$stats['messages']}</yellow> <cyan>message" . ($stats['messages'] == 1 ? '' : 's') . " have been sent</cyan>",
            "<yellow>{$user}</yellow> <cyan>is the most active with</cyan> <yellow>{$messages}</yellow> <cyan>message" . ($messages == 1 ? '' : 's') . "</cyan>",
            "<yellow>{$nick}</yellow> <cyan>nick change" . ($nick == 1 ? '' : 's') . "</cyan>",
            "<yellow>{$join}</yellow> <cyan>join" . ($join == 1 ? '' : 's') . "</cyan>",
            "<yellow>{$part}</yellow> <cyan>part" . ($part == 1 ? '' : 's') . "</cyan>",
            "<yellow>{$topic}</yellow> <cyan>topic change" . ($topic == 1 ? '' : 's') . "</cyan>",
        ];

        $channel->message("[ " . implode(' | ', $info) . " ]");
    });


hook('stats_record')
    ->on([
        'irc.packets.message.public',
        'irc.packets.action.public',
        'irc.bot.message.public',
        'irc.packets.kick',
        'irc.packets.nick',
        'irc.packets.join',
        'irc.packets.part',
        'irc.packets.topic',
        'irc.packets.quit'
    ])->func(function(Collection $args) {
        /** @var Channel $channel */
        $channel    = $args->get('channel');
        /** @var User $user */
        $user       = $args->get('user');

        $db         = database()->table('channels')->where('name', $channel->getLocation());
        $stats      = $db->first();
        $stats      = isset($stats['info']['stats']) ? $stats['info']['stats'] : [];

        switch($args->get('event'))
        {
            case 'irc.packets.message.public':
            case 'irc.packets.action.public':
            case 'irc.bot.message.public':
                $number     = 1;
                $userStat   = 1;

                if(isset($stats['messages']))
                    $number = $stats['messages'] + 1;

                if(isset($stats['users'][$user->nick()]))
                    $userStat = $stats['users'][$user->nick()] + 1;

                $stats['messages'] = $number;
                $stats['users'][$user->nick()] = $userStat;

                break;

            case 'irc.packets.kick':
                $stat     = 1;

                if(isset($stats['kick']))
                    $stat = $stats['kick'] + 1;

                $stats['kick'] = $stat;

                $stats['users'][$args->get('nick')] = $stats['users'][$user->nick()];

                unset($stats['users'][$user->nick()]);

                break;

            case 'irc.packets.nick':
                $stat     = 1;

                if(isset($stats['nick']))
                    $stat = $stats['nick'] + 1;

                $stats['nick'] = $stat;
                break;

            case 'irc.packets.join':
                $stat     = 1;

                if(isset($stats['join']))
                    $stat = $stats['join'] + 1;

                $stats['join'] = $stat;
                break;

            case 'irc.packets.part':
            case 'irc.packets.quit':
                $stat     = 1;

                if(isset($stats['part']))
                    $stat = $stats['part'] + 1;

                $stats['part'] = $stat;
                break;

            case 'irc.packets.topic':
                $stat     = 1;

                if(isset($stats['topic']))
                    $stat = $stats['topic'] + 1;

                $stats['topic'] = $stat;
                break;
        }

        $db->update(['info' => ['stats' => $stats]]);

    });