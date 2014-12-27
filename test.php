<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);


use Dan\Events\Event;
use Dan\Events\EventArgs;

require('./vendor/autoload.php');


Event::subscribe('test.packet', function(EventArgs $eventArgs){
    var_dump($eventArgs);
});

Event::subscribeOnce('test.packetOnce', function(EventArgs $eventArgs){
    var_dump($eventArgs);
});

Event::fire('test.packet', new EventArgs(['message' => 'first call']));
Event::fire('test.packetOnce', new EventArgs(['message' => 'one time call']));
Event::fire('test.packetOnce', new EventArgs(['message' => 'YOU SHOULD NOT SEE THIS']));

Event::fire('test.packet', new EventArgs(['message' => 'second call']));
Event::fire('test.packet', new EventArgs(['message' => 'third call']));