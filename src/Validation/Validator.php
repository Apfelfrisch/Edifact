<?php

namespace Apfelfrisch\Edifact\Validation;

use Apfelfrisch\Edifact\Exceptions\ValidationException;
use Apfelfrisch\Edifact\Message;
use Apfelfrisch\Edifact\Segment\SegmentCounter;
use Iterator;

class Validator
{
    private SegmentValidator $segmentValidator;

    /** @var Iterator<Failure|null>|null */
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
     * @return Iterator<Failure|null>
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

    /** @return Iterator<Failure|null> */
    private function validate(Message $message): Iterator
    {
        $counter = new SegmentCounter;

        foreach ($message->getSegments() as $segment) {
            $counter->count($segment);

            if (! ($segment instanceof Validateable)) {
                throw ValidationException::segmentNotValidateable($segment::class);
            }

            foreach ($segment->validate($this->segmentValidator) as $failure) {
                yield $failure->setMessageCounter($counter->messageCount())->setUnhCounter($counter->unhCount());
            }
        }
    }
}
