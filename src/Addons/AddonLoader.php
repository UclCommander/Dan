<?php

namespace Dan\Addons;

use Dan\Contracts\UserContract;
use Dan\Events\Traits\EventTrigger;

class AddonLoader
{
    use EventTrigger;

    protected $paths = [];

    public function __construct()
    {
        $this->addPath(addonsPath());
    }

    /**
     * Loads all addons
     *
     * @param \Dan\Contracts\UserContract $userContract
     */
    public function loadAll(UserContract $userContract = null)
    {
        console()->info('Loading all addons...');

        $this->triggerEvent('addons.load');

        foreach ($this->paths as $path) {
            $this->loadFromPath($path, $userContract);
        }

        console()->success('All addons loaded.');
    }

    /**
     * Adds a path to the loader.
     *
     * @param $path
     */
    public function addPath($path)
    {
        if (!in_array($path, $this->paths)) {
            $this->paths[] = $path;
        }
    }

    /**
     * Loads the given path.
     *
     * @param $path
     * @param \Dan\Contracts\UserContract $userContract
     */
    protected function loadFromPath($path, UserContract $userContract = null)
    {
        foreach (filesystem()->allFiles($path) as $file) {
            // This hook was disabled, ignore it.
            if (strpos(basename($file), '_') === 0) {
                continue;
            }

            try {
                include $file;
            } catch (\Error $error) {
                $name = basename($file);

                if (!is_null($userContract)) {
                    $userContract->notice("Unable to load addon {$name}. {$error->getMessage()}");
                }

                console()->error("Unable to load addon {$name}. {$error->getMessage()}");
                console()->exception($error);
            }
        }
    }
}
