<?php


use Illuminate\Support\Collection;

hook('users')
    ->command(['users'])
    ->console()
    ->func(function(Collection $args) {
        $users = [];

        foreach($args->get('channel')->getUsers() as $user)
            $users[] = $user['nick'];

        $args->get('user')->notice(implode(', ', $users));
    });