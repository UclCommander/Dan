<?php

/**
 * 8ball command. Predicts the future.
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */

use Illuminate\Support\Collection;

hook('8ball')
    ->command(['8ball', '8b'])
    ->help("Ask the 8ball a question")
    ->func(function (Collection $args){

        $channel = $args->get('channel');
        $message = $args->get('message');

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
            $channel->message("It appears the 8ball has nothing to say to... nothing.");
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
            $beginning = '<bang>*rolling 8ball intensifies*</bang>';

        $channel->message("<i>{$beginning}...</i> {$response}", ['bang' => ['red', null, ['b', 'i']]]);

    });
