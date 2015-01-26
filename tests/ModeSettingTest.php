<?php

/** @var Dan\Irc\Connection $connection */

$connection = require('TestBase.php');
$line = ":FlufflePuff MODE FlufflePuff :+iRx";


$connection->handleLine($line);

