<?php

if(!defined('STDIN')) // Eenope
    throw new Exception("CLI Only");

if(!defined('ROOT_DIR')) //Don't directly boot from this file.
    throw new Exception("Invalid loading");

define('CONFIG_DIR', ROOT_DIR . '/config');
define('STORAGE_DIR', ROOT_DIR . '/storage');
define('PLUGIN_DIR', ROOT_DIR . '/plugins');

require(__DIR__ . '/vendor/autoload.php');
require(__DIR__ . '/src/exceptions.php');

use Dan\Core\Dan;

return new Dan();