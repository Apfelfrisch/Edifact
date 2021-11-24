<?php

namespace Proengeno\Edifact\Interfaces;

use Proengeno\Edifact\Delimiter;

interface SegInterface
{
    public static function fromSegLine(Delimiter $delimiter, string $segLine): self|static;

    public function name(): string;

    public function validate(): void;

    /**
     * @psalm-return array<string, array<string, string|null>>
     */
    public function toArray(): array;

    public function toString(Delimiter $delimiter): string;
}
