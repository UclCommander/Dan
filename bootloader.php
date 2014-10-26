<?php

if(!defined('STDIN')) // Eenope
    throw new Exception("CLI Only");

if(!defined('ROOT_DIR')) //Don't directly boot from this file.
    throw new Exception("Invalid loading");

define('CONFIG_DIR', ROOT_DIR . '/config');
define('STORAGE_DIR', ROOT_DIR . '/storage');

require(__DIR__ . '/vendor/autoload.php');

use Dan\Core\Dan;

Dotenv::load(__DIR__);

return new Dan();
