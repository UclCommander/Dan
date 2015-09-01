<?php


use Illuminate\Support\Collection;

hook('users')
    ->command(['users'])
    ->console()
    ->func(function(Collection $args) {
        $users = [];

        $count = 0;

        foreach($args->get('channel')->getUsers() as $user)
        {
            if($count == 9)
            {
                $args->get('user')->notice(implode(', ', $users));
                $users = [];
                $count = 0;
                continue;
            }

            $users[] = '+' . implode('', $user['modes']->modes()->toArray()) . ":" . $user['nick'];

            $count++;
        }

        $args->get('user')->notice(implode(', ', $users));
    });