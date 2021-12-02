<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact;

use Apfelfrisch\Edifact\Interfaces\SegInterface;

trait SegmentCountTrait
{
    private int $unhCounter = 0;
    private int $messageCounter = 0;

    private function countSegments(SegInterface $segment): void
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
}
