<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Segment;

final class SegmentCounter
{
    private int $unhCounter = 0;
    private int $messageCounter = 0;

    public function count(Segment $segment): void
    {
        $segmentName = $segment->name();

        if ($segmentName === 'UNH') {
            $this->unhCounter = 1;
            $this->messageCounter++;

            return;
        }

        if ($segmentName !== 'UNB') {
            $this->unhCounter++;
        }
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
