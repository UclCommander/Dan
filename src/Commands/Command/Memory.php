<?php namespace Dan\Commands\Command;

use Dan\Commands\Command;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

class Memory extends Command {

    protected $defaultRank = 'S';

    /**
     * @inheritdoc
     */
    public function run(Channel $channel, User $user, $message)
    {
        $real = false;
        
        if($message == '--real')
            $real = true;
        
        $user->sendNotice(($real ? 'Real ' : '') . "Memory Usage: " . $this->convert(memory_get_usage($real)));
        $user->sendNotice(($real ? 'Real ' : '') . "Peak Memory Usage: " . $this->convert(memory_get_peak_usage($real)));
    }

    /**
     * @inheritdoc
     */
    public function help(User $user, $message)
    {
        $user->sendNotice("memory [--real] - Gets the [real] memory usage");
    }

    /**
     * Got from the php docs in the comment area because im lazy.
     *
     * @param $size
     * @return string
     */
    private function convert($size)
    {
        $unit=['b','kb','mb','gb','tb','pb'];
        return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
    }
}