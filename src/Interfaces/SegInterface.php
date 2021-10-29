<?php

namespace Proengeno\Edifact\Interfaces;

use Proengeno\Edifact\Message\Delimiter;

interface SegInterface
{
    public static function fromSegline(string $segLine, ?Delimiter $delimiter = null): SegInterface;

    /**
     * @return string
     */
    public function name();

    /**
     * @return static
     */
    public function validate(?SegValidatorInterface $validator = null);

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

    public function toString(?Delimiter $delimiter = null): string;
}
