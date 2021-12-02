<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Validation;

final class Failure
{
    public function __construct(
        public string $segmentName,
        public int $messageCounter,
        public int $unhCount,
        public string $elementKey,
        public string $componentKey,
        public mixed $value,
        public string $message
    ) { }
}
