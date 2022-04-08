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
    /** @psalm-var Iterator<Failure>|null */
    private ?Iterator $failures = null;

    public function __construct()
    {
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
        $counter = new SegmentCounter;

        foreach ($message->getSegments() as $segment) {
            $counter->count($segment);

            if (!($segment instanceof ValidateableInterface)) {
                throw ValidationException::segmentNotValidateable($segment::class);
            }

            foreach ($segment->validate($this->segmentValidator) as $failure) {
                yield $failure->setMessageCounter($counter->messageCount())->setUnhCounter($counter->unhCount());
            }
        }
    }
}
