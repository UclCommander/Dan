<?php
/**
 * Created by PhpStorm.
 * User: aaronash
 * Date: 6/4/15
 * Time: 9:44 PM
 */

namespace Dan\Plugins;


class PluginAutoloader {

    protected $map = [];

    public function __construct()
    {
        spl_autoload_register([$this, 'loader']);
    }

    public function registerPlugin($plugin, $path, $hash)
    {
        $this->map[$plugin.$hash] = [
            'path'  => $path,
            'hash'  => $hash,
            'class' => $plugin,
        ];
    }

    public function unregisterPlugin($plugin)
    {
        unset($this->map[$plugin]);
    }


    public function loader($class)
    {
        $data = explode('\\', $class, 2);

        if(!array_key_exists($data[0], $this->map))
            return;

        $info = $this->map[$data[0]];

        $file = $info['path'] . str_replace('\\', '/', $data[1]) . '.php';

        if(!file_exists($file))
            return;

        $fileData = file_get_contents($file);

        $fileData = str_replace([
            "namespace {$info['class']}",
            "use {$info['class']}\\",
        ], [
            "namespace {$info['class']}{$info['hash']}",
            "use {$info['class']}{$info['hash']}\\",
        ], $fileData);

        $fileData = preg_replace("/<\?php/", '', $fileData, 1);

        if($fileData == null)
            throw new \Exception("Error loading plugin class {$class}");

        eval($fileData);
    }
}