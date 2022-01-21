<?php

namespace Apfelfrisch\Edifact\Validation;

use Apfelfrisch\Edifact\Exceptions\ValidationException;
use Apfelfrisch\Edifact\Validation\ValidateableInterface;
use Apfelfrisch\Edifact\Message;
use Apfelfrisch\Edifact\Segment\SegmentCounter;
use Iterator;

class Validator
{
    private SegmentValidator $segmentValidator;
    private SegmentCounter $counter;
    /** @psalm-var Iterator<Failure>|null */
    private ?Iterator $failures = null;

    public function __construct()
    {
        $this->counter = new SegmentCounter;
        $this->segmentValidator = new SegmentValidator();
    }

    public function isValid(Message $message): bool
    {
        $this->failures = $this->validate($message);

        return $this->failures->current() === null;
    }

    /**
     * @throws ValidationException
     *
     * @psalm-return Iterator<Failure>
     */
    public function getFailures(): Iterator
    {
        if ($this->failures === null) {
            throw ValidationException::messageNotValidated();
        }

        return $this->failures;
    }

    /**
     * @throws ValidationException
     */
    public function getFirstFailure(): Failure|null
    {
        return $this->getFailures()->current();
    }

    /** @psalm-return Iterator<Failure> */
    private function validate(Message $message): Iterator
    {
        foreach ($message->getSegments() as $segment) {
            $this->counter->count($segment);

            if (!($segment instanceof ValidateableInterface)) {
                throw ValidationException::segmentNotValidateable($segment::class);
            }

            foreach ($segment->validate($this->segmentValidator) as $failure) {
                yield $failure->setMessageCounter($this->counter->messageCount())->setUnhCounter($this->counter->unhCount());
            }
        }
    }
}
