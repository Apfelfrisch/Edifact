<?php

namespace Apfelfrisch\Edifact\Interfaces;

use Apfelfrisch\Edifact\Delimiter;

interface SegInterface
{
    public static function fromSegLine(Delimiter $delimiter, string $segLine): self|static;

    public function name(): string;

    public function validate(): void;

    public function getValue(string $dataGroupKey, string $valueKey): ?string;

    public function getValueFromPosition(int $dataGroupPosition, int $valuePosition): ?string;

    /**
     * @psalm-return array<string, array<string, string|null>>
     */
    public function toArray(): array;

    public function toString(Delimiter $delimiter): string;
}
