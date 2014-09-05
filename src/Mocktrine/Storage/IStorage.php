<?php

namespace Mocktrine\Storage;

interface IStorage
{
    const OVERWRITE = true;
    public function save($key, $value);
    public function get($key);
    public function has($key);
    public function delete($key);
    public function flush();
    public function persist();
}
