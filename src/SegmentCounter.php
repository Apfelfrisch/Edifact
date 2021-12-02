<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact;

use Apfelfrisch\Edifact\Interfaces\SegInterface;

final class SegmentCounter
{
    private int $unhCounter = 0;
    private int $messageCounter = 0;

    public function count(SegInterface $segment): void
    {
        if ($segment->name() === 'UNA' || $segment->name() === 'UNB') {
            return;
        }

        if ($segment->name() === 'UNH') {
            $this->unhCounter = 1;
            $this->messageCounter++;
            return;
        }

        $this->unhCounter++;
    }

    public function messageCount(): int
    {
        return $this->messageCounter;
    }

    public function unhCount(): int
    {
        if ($this->messageCounter === 0) {
            return $this->messageCounter;
        }

        return $this->unhCounter;
    }
}
