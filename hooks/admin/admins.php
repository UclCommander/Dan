<?php

/**
 * Admins command. Lists the bot admins and owners.
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */

use Dan\Core\Dan;
use Illuminate\Support\Collection;

hook('admins')
    ->command(['admins', 'owner', 'owners'])
    ->console()
    ->help('Lists the bot admins and owners.')
    ->func(function(Collection $args) {

        $users = database()->table('users')->get();

        $owners = [];
        $admins = [];

        foreach ($users as $user) {
            $user = user($user);

            if (Dan::isOwner($user)) {
                $owners[] = $user->nick();
            }

            if (Dan::isAdmin($user)) {
                $admins[] = $user->nick();
            }
        }

        $args->get('user')->notice("Owners: " . implode(', ', $owners));
        $args->get('user')->notice("Admins: " . implode(', ', $admins));
    });
