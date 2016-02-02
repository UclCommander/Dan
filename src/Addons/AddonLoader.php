<?php

namespace Dan\Addons;

use Dan\Events\Traits\EventTrigger;

class AddonLoader
{
    use EventTrigger;

    public function loadAll()
    {
        console()->info('Loading all addons...');

        $this->triggerEvent('addons.load');

        foreach (filesystem()->allFiles(addonsPath()) as $file) {
            // This hook was disabled, ignore it.
            if (strpos(basename($file), '_') === 0) {
                continue;
            }

            try {
                include $file;
            } catch (\Error $error) {
                $name = basename($file);
                console()->error("Unable to load addon {$name}. {$error->getMessage()}");
            }
        }

        console()->success('All addons loaded.');
    }
}
