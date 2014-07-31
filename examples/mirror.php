<?php

require_once '../loader.php';

$serviceLocator = new \Mocktrine\ServiceLocator();
$server = new \Mocktrine\Server($serviceLocator);