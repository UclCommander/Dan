<?php

use Dan\Helpers\Web;
use Illuminate\Support\Collection;

hook('cyanide')
    ->command(['cyanide', 'cy', 'ch'])
    ->func(function(Collection $args) {
        $id = intval($args->get('message'));

        if(!$id)
            $id = 'random';

        $url = get_final_url('http://explosm.net/comics/' . $id);

        $comic  = Web::dom($url);
        $image  = $comic->getElementById('main-comic');
        $src    = "http:" . $image->getAttribute('src');

        $id = last(array_filter(explode('/', $url)));

        $args->get('channel')->message("[ <yellow>#{$id}</yellow> | <cyan>{$src}</cyan> ]");
    });


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

hook('fgtlist')
    ->command(['fgtlist', 'fgts'])
    ->help('Gets da fgts')
    ->func(function(Collection $args) {
        $list = [
            'Chris',
            'Mirz <3',
            'RoboDash',
        ];

        foreach($list as $fgt)
            $args->get('user')->notice($fgt);
    });

hook('lenny')
    ->command(['lenny'])
    ->help('lenny faces. Optional: hugs, no, lenninati, backward(s), pumped')
    ->func(function(Collection $args) {
        switch(trim($args->get('message')))
        {
            case "hugs":
                $lenny = "(つ ͡° ͜ʖ ͡°)つ";
                break;

            case "no":
                $lenny = "( ͡°_ʖ ͡°)";
                break;

            case "lenninati":
                $lenny = "( ͡∆ ͜ʖ ͡∆)";
                break;

            case "backward":
            case "backwards":
                $lenny = "( °͡ ʖ͜ °͡  )";
                break;

            case "pumped":
                $lenny = "(ง ͠° ͟ل͜ ͡°)ง";
                break;

            default:
                $lenny = "( ͡° ͜ʖ ͡°)";
                break;
        }

        $args->get('channel')->message($lenny);
    });

hook('booty')
    ->command(['booty'])
    ->help('look at that booty, show me the booty, gimme the booty, i want the booty, back up the booty, i need the booty, i like the booty, oh what a booty, shakin that booty, i saw the booty, i want the booty, lord want the booty, bring on the booty, give up the booty, lovin the booty, round booty, down for the booty, i want the booty, huntin the booty, chasin the booty, casin the booty, getting the booty, beautiful booty, smokin booty, talk to the booty, more booty, fine booty')
    ->func(function(Collection $args) {
        $args->get('channel')->message('https://youtu.be/wGlBwW7f5HA');
    });


hook('slap')
    ->command(['slap'])
    ->help('Slaps someone')
    ->func(function (Collection $args){
        $message    = $args->get('message');
        $channel    = $args->get('channel');
        $user       = $args->get('user');

        $data = explode(' ', $message, 2);

        if($data[0] == connection()->user->nick())
        {
            $channel->message("Hey! That's rude!");
            $channel->action("smacks {$user->nick()} on the back of the head");
            return;
        }

        $verb = array_random([
            'smacks', 'kicks', 'slaps', 'chops',
            'rekts', 'kills', 'blows up', 'annihilates',
            'roundhouse kicks',
        ]);

        $after = array_random([
            'into a wall', 'into space', 'to death', 'out of the channel',
            'into a pancake', 'into a bacon pancake',
            'into a cupcake'
        ]);

        $channel->action("{$verb} {$data[0]} {$after}");
    });

hook('whoopass')
    ->command(['whoopass'])
    ->help("When a normal beating just won't do!")
    ->func(function(Collection $args) {
        if($args->get('message') && $args->get('user')->hasOneOf('hoaq'))
        {
            $data = explode(' ', $args->get('message'));

            send('KICK', $args->get('channel'), $data[0], "When a normal beating just won't do! WHOOPASS! Extra strength! http://skycld.co/whoopass");
            return;
        }

        $args->get('channel')->message("When a normal beating just won't do! http://skycld.co/whoopass");
    });