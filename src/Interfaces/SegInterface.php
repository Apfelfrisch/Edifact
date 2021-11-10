<?php

namespace Proengeno\Edifact\Interfaces;

use Proengeno\Edifact\Message\Delimiter;

interface SegInterface
{
    public static function fromSegLine(Delimiter $delimiter, string $segLine): SegInterface;

    public function getDelimiter(): Delimiter;

    public function name(): string;

    public function validate(): void;

    public function toArray(): array;

    public function toString(): string;
}
