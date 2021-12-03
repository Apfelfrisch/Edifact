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
        public int $unhCount = 0,
    ) { }

    public function setMessageCount(int $count): self
    {
        $this->messageCounter = $count;

        return $this;
    }

    public function setUnhCount(int $count): self
    {
        $this->unhCount = $count;

        return  $this;
    }
}
