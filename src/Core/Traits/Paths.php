<?php

namespace Dan\Core\Traits;

trait Paths
{
    /**
     * Create all paths.
     */
    public function createPaths()
    {
        if (!filesystem()->exists($this->configPath())) {
            filesystem()->makeDirectory($this->configPath(), 0775, true);
        }

        if (!filesystem()->exists($this->storagePath())) {
            filesystem()->makeDirectory($this->storagePath(), 0775, true);
        }

        if (!filesystem()->exists($this->databasePath())) {
            filesystem()->makeDirectory($this->databasePath(), 0775, true);
        }

        if (!filesystem()->exists($this->addonsPath())) {
            filesystem()->makeDirectory($this->addonsPath(), 0775, true);
        }
    }

    /**
     * Bind all the paths to the container.
     */
    protected function bindPathsInContainer()
    {
        foreach (['root', 'base', 'config', 'addons', 'src', 'database', 'storage'] as $path) {
            $this->instance('path.'.$path, $this->{$path.'Path'}());
        }
    }

    /**
     * @return string
     */
    protected function rootPath()
    {
        return ROOT_DIR;
    }

    /**
     * @return string
     */
    protected function basePath()
    {
        return $this->rootPath();
    }

    /**
     * @return string
     */
    protected function configPath()
    {
        return $this->basePath().'/config';
    }

    /**
     * @return string
     */
    protected function addonsPath()
    {
        return $this->basePath().'/addons';
    }

    /**
     * @return string
     */
    protected function srcPath()
    {
        return $this->basePath().'/src';
    }

    /**
     * @return string
     */
    protected function databasePath()
    {
        return $this->storagePath().'/database';
    }

    /**
     * @return string
     */
    protected function storagePath()
    {
        return $this->basePath().'/storage';
    }
}
