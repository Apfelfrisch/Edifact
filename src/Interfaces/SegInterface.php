<?php

namespace Proengeno\Edifact\Interfaces;

use Proengeno\Edifact\Message\Delimiter;

interface SegInterface {
    /**
     * @param string $segLine
     *
     * @return static
     */
    public static function fromSegLine($segLine);

    /**
     * @return void
     */
    public static function setBuildDelimiter(Delimiter $delimiter);

    /**
     * @return Delimiter
     */
    public static function getBuildDelimiter();

    /**
     * @return Delimiter
     */
    public function getDelimiter();

    /**
     * @return string
     */
    public function name();

    /**
     * @return static
     */
    public function validate();

    /**
     * @return array
     */
    public function toArray();

    /**
     * @param string $attribute
     *
     * @return string|null
     */
    public function __get($attribute);

    /**
     * @return string
     */
    public function __toString();
}
