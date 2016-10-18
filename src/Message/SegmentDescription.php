<?php

namespace Proengeno\Edifact\Message;

use Proengeno\Edifact\Exceptions\SegmentDesciptionException;

class SegmentDescription
{
    public static $instanceLookup = [];

    private $jsonPath;
    private $method;

    private function __construct($jsonPath = null)
    {
        $this->json = $jsonPath ? json_decode(file_get_contents($jsonPath), 1) : '{}';
    }

    public function make($jsonPath = null)
    {
        if (!isset(self::$instanceLookup[$jsonPath])) {
            self::$instanceLookup[$jsonPath] = new self($jsonPath);
        }
        return self::$instanceLookup[$jsonPath];
    }

    public function description($method, $key)
    {
        return $this->getData('description', $method, $key);
    }

    public function name($method, $key)
    {
        return $this->getData('name', $method, $key);
    }

    public function tags($method, $key)
    {
        return $this->getData('tags', $method, $key);
    }

    public function keys($method)
    {
        if (!isset($this->json[$method])) {
            throw new SegmentDesciptionException("No $catergory available for Method named '$method'.");
        }

        return array_keys($this->json[$method]);
    }

    private function getData($catergory, $method, $key)
    {
        if (!isset($this->json[$method])) {
            throw new SegmentDesciptionException("No $catergory available for Method named '$method'.");
        }
        if (!isset($this->json[$method][$key])) {
            throw new SegmentDesciptionException("No $catergory available for Key named '$key'.");
        }

        return $this->json[$method][$key][$catergory];
    }
}
