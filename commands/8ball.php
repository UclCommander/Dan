<?php

use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;
use Illuminate\Support\Arr;

/** @var User $user */
/** @var Channel $channel */
/** @var string $message */
/** @var string $entry */

if($entry == 'use')
{
    $positive = [
        "It is certain", "It is decidedly so", "Without a doubt",
        "Yes definitely", "You may rely on it", "As I see it, yes",
        "Most likely", "Outlook good", "Yes", "Signs point to yes"
    ];

    $neutral = [
        "Reply hazy try again", "Ask again later", "Better not tell you now",
        "Cannot predict now", "Concentrate and ask again"
    ];

    $negative = [
        "Don't count on it", "My reply is no", "My sources say no",
        "Outlook not so good", "Very doubtful"
    ];

    if(empty($message))
    {
        message($channel, "It appears the 8ball has nothing to say to... nothing.");
        return;
    }

    $rand = rand(1, 4);

    if($rand <= 2)
    {
        $response = $positive[array_rand($positive)];
    }
    else if($rand == 3)
    {
        $response = $neutral[array_rand($neutral)];
    }
    else
    {
        $response = $negative[array_rand($negative)];
    }

    $beginning = '*rolls 8ball*';

    if(rand(0, 5) == 2)
        $beginning = '{b}{red}*rolling 8ball intensifies*{reset}';

    message($channel, "{i}{$beginning}...{r} {$response}");
}

if($entry == 'help')
{
    return [
        "{cp}8ball <question> - Ask the magical 8ball a question."
    ];
}