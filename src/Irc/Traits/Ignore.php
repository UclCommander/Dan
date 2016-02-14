<?php namespace Dan\Irc\Traits;


use Dan\Irc\Location\User;
use Illuminate\Support\Collection;

trait Ignore
{
    /**
     * @param \Dan\Irc\Location\User $user
     *
     * @return bool
     */
    public function isIgnored(User $user) : bool
    {
        /** @var Collection $data */
        $data = $this->connection->database('ignore')->get();

        if ($this->connection->isAdminOrOwner($user)) {
            return false;
        }

        foreach ($data as $mask) {
            if (fnmatch($mask['mask'], $user)) {
                return true;
            }
        }

        return false;
    }
}