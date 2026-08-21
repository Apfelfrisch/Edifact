<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Segment;

interface Segment
{
    public static function fromSegLine(SeglineParser $parser, string $segLine): static;

    public function name(): string;

    public function getValue(string $elementKey, string $componentKey): ?string;

    public function getValueFromPosition(int $elementPosition, int $valuePosition): ?string;

    public function isValueNumeric(string $elementKey, string $componentKey): bool;

    /** @return array<array-key, array<array-key, string|null>> */
    public function toArray(): array;
}
