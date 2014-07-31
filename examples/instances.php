<?php

require_once __DIR__ . '/../loader.php';

$inst1 = new \Mocktrine\Pool();

$inst2 = new \Mocktrine\Pool();

$inst1->getStorage()->save('bar', 'bar');

echo $inst2->getStorage()->get('bar');