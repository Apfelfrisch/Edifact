<?php

namespace Proengeno\Edifact\Validation;

use Proengeno\Edifact\Message;
use Proengeno\Edifact\Interfaces\SegInterface;
use Proengeno\Edifact\Exceptions\EdifactException;
use Proengeno\Edifact\Exceptions\ValidationException;
use Proengeno\Edifact\Exceptions\SegValidationException;

/*
 * Todo: Klasse komplett neuschreiben, die ist Mist
 */
class MessageValidator
{
    private int $lineCount = 0;

    /**
     * @param Message $edifact
     *
     * @return self
     */
    public function validate($edifact)
    {
        $validation = $edifact->getDescription('validation');

        if (! is_array($validation)) {
            throw new ValidationException("No validation configuration provided", 0, null);
        }

        try {
            $edifact->pinPointer();
            $edifact->rewind();

            $this->loop($edifact, new Blueprint($validation));

            $edifact->jumpToPinnedPointer();

            return $this;
        } catch (EdifactException $e) {
            throw new ValidationException($e->getMessage(), $this->lineCount, null);
        }
    }

    private function loop(Message $edifact, Blueprint $validator): void
    {
        while ($line = $edifact->getNextSegment()) {
            $this->lineCount++;
            $validator->validate($line);
            $this->validateSegment($line);
        }
    }

    private function validateSegment(SegInterface $segment): void
    {
        try {
            $segment->validate();
        } catch (SegValidationException $e) {
            throw new ValidationException($e->getMessage(), null, @$segment->name());
        }
    }
}
