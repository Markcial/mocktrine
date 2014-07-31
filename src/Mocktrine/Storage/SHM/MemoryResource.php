<?php


namespace Mocktrine\Storage\SHM;


/**
 * Class MemoryResource
 * @package Mocktrine\Storage\SHM
 */
class MemoryResource
{
    private $filePath;

    public function __construct($filePath = null)
    {
        if (is_null($filePath)) {
            $filePath = '/tmp/mocktrine.shared.resource';
        }
        if (!file_exists($filePath)) {
            touch($filePath);
        }
        $this->filePath = $filePath;
    }

    public function getFilePath()
    {
        return $this->filePath;
    }

    public function getFtOk()
    {
        return ftok($this->filePath, 'a');
    }
} 