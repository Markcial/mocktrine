<?php
require_once __DIR__ . '/../../loader.php';

$pool = new \Mocktrine\Pool();
$foo = $pool->getStorage()->get('foo');
var_dump($foo);