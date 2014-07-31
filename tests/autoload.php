<?php
require_once dirname(__FILE__) . '/../loader.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespace("TestPackage", __DIR__);
$loader->register();