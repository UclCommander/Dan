<?php

namespace Dan\Addons;

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
     */
    public function loadAll()
    {
        console()->info('Loading all addons...');

        $this->triggerEvent('addons.load');

        foreach ($this->paths as $path) {
            $this->loadFromPath($path);
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
     */
    protected function loadFromPath($path)
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

                if (!config('dan.debug')) {
                    console()->error("Unable to load addon {$name}. {$error->getMessage()}");
                } else {
                    console()->exception($error);
                }
            }
        }
    }
}
