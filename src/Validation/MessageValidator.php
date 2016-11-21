<?php

namespace Proengeno\Edifact\Validation;

use Proengeno\Edifact\Message\Message;
use Proengeno\Edifact\Interfaces\SegInterface;
use Proengeno\Edifact\Exceptions\EdifactException;
use Proengeno\Edifact\Exceptions\ValidationException;
use Proengeno\Edifact\Exceptions\SegValidationException;

/*
 * Todo: Klasse komplett neuschreiben, die ist Mist
 */
class MessageValidator
{
    private $blueprint;
    private $lineCount = 0;
    private $blueprintValidator;

    public function validate(Message $edifact)
    {
        $edifact->pinPointer();

        $this->blueprintValidator = new Blueprint($edifact->getDescription('validation'));

        $edifact->rewind();

        try {
            $this->loop($edifact);
        } catch (EdifactException $e) {
            throw new ValidationException($e->getMessage(), $this->lineCount, null);
        }

        $edifact->jumpToPinnedPointer();

        return $this;
    }

    public function loop($edifact)
    {
        while ($line = $edifact->getNextSegment()) {
            $this->lineCount++;
            $this->blueprintValidator->validate($line);
            $this->validateSegment($line);
        }
    }

    private function validateSegment(SegInterface $segment)
    {
        try {
            $segment->validate();
        } catch (SegValidationException $e) {
            throw new ValidationException($e->getMessage(), null, @$segment->name());
        }
    }
}
