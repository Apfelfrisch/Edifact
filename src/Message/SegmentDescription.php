<?php

namespace Proengeno\Edifact\Message;

use Proengeno\Edifact\Exceptions\SegmentDesciptionException;

class SegmentDescription
{
    private $jsonPath;
    private $method;

    public function __construct($jsonPath = null)
    {
        $this->json = $jsonPath ? json_decode(file_get_contents($jsonPath)) : '{}';
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

    private function getData($catergory, $method, $key)
    {
        if (!isset($this->json->$method)) {
            throw new SegmentDesciptionException("No $catergory available for Method named '$method'.");
        }
        if (!isset($this->json->$method->$key)) {
            throw new SegmentDesciptionException("No $catergory available for Key named '$key'.");
        }

        return $this->json->$method->$key->$catergory;
    }
}
