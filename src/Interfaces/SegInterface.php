<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Interfaces;

use Apfelfrisch\Edifact\Delimiter;
use Apfelfrisch\Edifact\SeglineParser;

interface SegInterface
{
    public static function fromSegLine(SeglineParser $parser, string $segLine): self|static;

    public function setDelimiter(Delimiter $delimiter): void;

    public function name(): string;

    public function validate(): void;

    public function getValue(string $elementKey, string $componentKey): ?string;

    public function getValueFromPosition(int $elementPosition, int $valuePosition): ?string;

    /**
     * @psalm-return array<string, array<string, string|null>>
     */
    public function toArray(): array;

    public function toString(): string;
}
