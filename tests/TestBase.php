<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

define("ROOT_DIR", dirname(__DIR__));

define('CONFIG_DIR', ROOT_DIR . '/config');
define('STORAGE_DIR', ROOT_DIR . '/storage');
define('PLUGIN_DIR', ROOT_DIR . '/plugins');

require('../vendor/autoload.php');

Dotenv::load(ROOT_DIR);

Dan\Core\Config::load();
