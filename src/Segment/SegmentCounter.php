<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Segment;

use Apfelfrisch\Edifact\Segment\SegmentInterface;

final class SegmentCounter
{
    private int $unhCounter = 0;
    private int $messageCounter = 0;

    public function count(SegmentInterface $segment): void
    {
        if ($segment->name() === 'UNB') {
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
        return $this->messageCounter > 0 ? $this->unhCounter : 0;
    }
}
