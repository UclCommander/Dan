<?php

namespace Dan\Contracts;

interface HookTypeContract
{
    /**
     * Registers an anonymous class to the hook.
     *
     * @param $anonymous
     *
     * @return void
     */
    public function anon($anonymous);

    /**
     * Registers an anonymous function to the hook.
     *
     * @param callable $callable
     *
     * @return void
     */
    public function func(callable $callable);

    /**
     * Runs the hook.
     *
     * @param $args
     *
     * @return bool
     */
    public function run($args);
}
