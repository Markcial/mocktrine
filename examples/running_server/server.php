<?php
require_once __DIR__ . '/../../loader.php';

$foo = new stdClass;
$foo->eggs = "bacon";

$server = new \Mocktrine\Server();
$server->getPool()->getStorage()->save('foo', $foo);
$server->serve();