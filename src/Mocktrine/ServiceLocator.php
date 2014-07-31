<?php


namespace Mocktrine;


/**
 * Class ServiceLocator
 * @package Mocktrine
 */
class ServiceLocator
{
    protected $services = array();

    public function get($namespace)
    {
        return $this->services[$namespace];
    }

    public function register($namespace, $service)
    {
        $this->services[$namespace] = $service;
    }
}