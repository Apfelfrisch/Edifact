<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact;

use Apfelfrisch\Edifact\Exceptions\BuildException;
use Apfelfrisch\Edifact\Formatter\EdifactFormatter;
use Apfelfrisch\Edifact\Segment\GenericSegment;
use Apfelfrisch\Edifact\Segment\SegmentCounter;
use Apfelfrisch\Edifact\Segment\SegmentInterface;
use Apfelfrisch\Edifact\Segment\UnaSegment;
use Apfelfrisch\Edifact\Stream\Stream;

class Builder
{
    private ?string $unbRef = null;
    private ?string $unhRef = null;

    private bool $buildIsFinalized = false;

    private Stream $stream;
    private string $filepath;
    private EdifactFormatter $stringFormatter;
    private SegmentCounter $counter;

    public function __construct(UnaSegment $unaSegment = null, string $filepath = 'php://temp')
    {
        $this->filepath = $filepath;

        $this->stream = new Stream($this->filepath, 'w+', $unaSegment);
        $this->counter = new SegmentCounter();
        $this->stringFormatter = new EdifactFormatter($this->stream->getUnaSegment());
        $this->stringFormatter->prefixUna();
    }

    public function addStreamFilter(string $filtername, mixed $params = null): self
    {
        $this->stream->addWriteFilter($filtername, $params);

        return $this;
    }

    public function __destruct()
    {
        // Delete File if build process could not finshed
        $filepath = $this->stream->getRealPath();
        if ($this->buildIsFinalized === false && $filepath && file_exists($filepath)) {
            unlink($filepath);
        }
    }

    public function getMessageCount(): int
    {
        return $this->counter->messageCount();
    }

    public function writeSegments(SegmentInterface ...$segments): void
    {
        if ($this->buildIsFinalized) {
            throw BuildException::finalizedBuildNotModifiable();
        }

        foreach ($segments as $segment) {
            if ($segment->name() === 'UNB') {
                $this->unbRef = $segment->getValueFromPosition(5, 0) ?? '';
            }

            if ($segment->name() === 'UNH') {
                if ($this->unhRef !== null) {
                    $unhCount = $this->counter->unhCount() + 1;
                    $this->writeSegment(
                        GenericSegment::fromAttributes('UNT', [(string)$unhCount], [$this->unhRef])
                    );
                }

                $this->unhRef = $segment->getValueFromPosition(1, 0) ?? '';
            }

            $this->writeSegment($segment);
        }
    }

    private function writeSegment(SegmentInterface $segment): void
    {
        $this->stream->write(
            $this->stringFormatter->format($segment)
        );

        $this->counter->count($segment);
    }

    public function get(): Stream
    {
        if (! $this->messageIsEmpty()) {
            if ($this->unhRef !== null) {
                $unhCount = $this->counter->unhCount() + 1;
                $this->writeSegment(
                    GenericSegment::fromAttributes('UNT', [(string)$unhCount], [$this->unhRef])
                );
                $this->unhRef = null;
            }
            if ($this->unbRef !== null) {
                $this->writeSegment(
                    GenericSegment::fromAttributes('UNZ', [(string)$this->counter->messageCount()], [$this->unbRef])
                );
                $this->unbRef = null;
            }
        }

        $this->buildIsFinalized = true;

        return $this->stream;
    }

    public function messageIsEmpty(): bool
    {
        return $this->stream->isEmpty();
    }
}
