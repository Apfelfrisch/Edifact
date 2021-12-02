<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Validation;

final class Failure
{
    public function __construct(
        public string $segmentName,
        public string $elementKey,
        public string $componentKey,
        public mixed $value,
        public string $message,
        public int $messageCounter = 0,
        public int $unhCounter = 0,
    ) { }

    public function setMessageCounter(int $messageCounter): self
    {
        $this->messageCounter = $messageCounter;

        return $this;
    }

    public function setUnhCounter(int $unhCounter): self
    {
        $this->unhCounter = $unhCounter;

        return $this;
    }
}
