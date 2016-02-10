<?php

namespace Dan\Irc\Traits;

use Dan\Irc\Location\User;

trait BotStaff
{

    /**
     * Checks to see if a user is an admin or owner.
     *
     * @param \Dan\Irc\Location\User $user
     *
     * @return bool
     */
    public function isAdminOrOwner(User $user)
    {
        return $this->isOwner($user) || $this->isAdmin($user);
    }

    /**
     * Checks to see if the given user is a bot admin.
     *
     * @param User $user
     *
     * @return bool
     */
    public function isAdmin(User $user) : bool
    {
        foreach (config('dan.admins') as $usr) {
            if (fnmatch($usr, $user)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checks to see if the given user is a bot owner.
     *
     * @param User $user
     *
     * @return bool
     */
    public function isOwner(User $user) : bool
    {
        foreach (config('dan.owners') as $usr) {
            if (fnmatch($usr, $user)) {
                return true;
            }
        }
        return false;
    }
}