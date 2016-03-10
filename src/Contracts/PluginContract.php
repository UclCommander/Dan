<?php

namespace Dan\Contracts;

interface PluginContract
{
    /**
     * Returns the plugin's name.
     *
     * @return string
     */
    public function getName();

    /**
     * Registers the plugin.
     *
     * @return void
     */
    public function register();

    /**
     * Creates the config file for the plugin.
     *
     * @return array
     */
    public function config() : array;
}