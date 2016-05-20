<?php

namespace Dan\Irc\Traits;

/**
 * Class IrcDatabase.
 *
 *
 * @property string $name
 */
trait IrcDatabase
{
    public function createDatabase()
    {
        if (!database()->exists($this->name)) {
            database()->create($this->name);
        }

        if (!database($this->name)->tableExists('channels')) {
            database($this->name)
                ->schema('channels')
                ->create([
                    'name'      => '',
                    'max_users' => 0,
                    'topic'     => '',
                    'data'      => [],
                ]);
        }

        if (!database($this->name)->tableExists('users')) {
            database($this->name)
                ->schema('users')
                ->create([
                    'nick'  => '',
                    'user'  => '',
                    'host'  => '',
                    'real'  => '',
                    'data'  => [],
                ]);
        }

        if (!database($this->name)->tableExists('ignore')) {
            database($this->name)
                ->schema('ignore')
                ->create(['mask'  => '']);
        }

        if (!database($this->name)->tableExists('cache')) {
            database($this->name)
                ->schema('cache')
                ->create([
                    'key'   => '',
                    'value' => [],
                ]);
        }

        if (!database($this->name)->tableExists('remind')) {
            database($this->name)
                ->schema('remind')
                ->create([
                    'remind' => '',
                    'when'   => 0,
                    'what'   => '',
                ]);
        }
    }
}
