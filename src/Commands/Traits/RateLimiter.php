<?php

namespace Dan\Commands\Traits;

use Carbon\Carbon;
use Dan\Commands\Command;
use Dan\Irc\Location\User;

trait RateLimiter
{
    protected $rateCheck = [];

    protected $spamCheck = [];

    /**
     * @param \Dan\Irc\Location\User $user
     * @param \Dan\Commands\Command $command
     *
     * @return bool
     */
    public function checkRate(User $user, Command $command)
    {
        if (!array_key_exists($user->id, $this->rateCheck)) {
            return false;
        }

        $userRate = $this->rateCheck[$user->id];
        $command = $command->getAlias();

        if (!array_key_exists($command, $userRate)) {
            return false;
        }

        $used = $userRate[$command][0];
        $diff = (new Carbon())->diffInSeconds($userRate[$command][1]);

        $commandRate = config("rate.commands.{$command}", config('rate.default'));

        // So I remember what this does in the future:
        // If the command is used more than the command rate limit
        // and the diff is less than or equal to the rate in seconds
        // return true and reset Carbon to the current time to prevent mass spam
        if ($used >= $commandRate[0] && $diff <= $commandRate[1]) {
            $this->spamCheck[$user->id]++;
            $this->rateCheck[$user->id][$command][1] = new Carbon();

            return true;
        }

        if ($diff > $commandRate[1]) {
            $this->rateCheck[$user->id][$command] = [0, new Carbon()];
        }

        return false;
    }

    /**
     * @param \Dan\Irc\Location\User $user
     * @param Command $command
     */
    public function addRate(User $user, Command $command)
    {
        if (!array_key_exists($user->id, $this->rateCheck)) {
            $this->rateCheck[$user->id] = [];
            $this->spamCheck[$user->id] = 0;
        }

        if (!array_key_exists($command->getAlias(), $this->rateCheck[$user->id])) {
            $this->rateCheck[$user->id][$command->getAlias()] = [0, new Carbon()];
        }

        $this->rateCheck[$user->id][$command->getAlias()][0] += 1;
    }

    /**
     * @param \Dan\Irc\Location\User $user
     *
     * @return bool
     */
    public function isSpamming(User $user)
    {
        return $this->spamCheck[$user->id] > config('rate.kick_from_spam');
    }
}
