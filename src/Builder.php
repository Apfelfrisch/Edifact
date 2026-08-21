<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact;

use Apfelfrisch\Edifact\Exceptions\BuildException;
use Apfelfrisch\Edifact\Formatter\EdifactFormatter;
use Apfelfrisch\Edifact\Iterators\Stream\Stream;
use Apfelfrisch\Edifact\Segment\GenericSegment;
use Apfelfrisch\Edifact\Segment\Segment;
use Apfelfrisch\Edifact\Segment\SegmentCounter;
use Apfelfrisch\Edifact\Segment\UnaSegment;

final class Builder
{
    private const int WRITE_BUFFER_SIZE = 65_536;

    private string $writeBuffer = '';

    private null|string $unbRef = null;

    private null|string $unhRef = null;

    private bool $buildIsFinalized = false;

    private readonly Stream $stream;

    private readonly EdifactFormatter $stringFormatter;

    private readonly SegmentCounter $counter;

    public function __construct(null|UnaSegment $unaSegment = null, string $filepath = 'php://temp')
    {
        $this->stream = new Stream($filepath, 'w+', $unaSegment);
        $this->counter = new SegmentCounter();
        $this->stringFormatter = new EdifactFormatter($this->stream->getUnaSegment());
        $this->stringFormatter->prefixUna();
    }

    public function __destruct()
    {
        // Delete the file if the build process could not be finished
        $filepath = $this->stream->getRealPath();

        if ($this->buildIsFinalized === false && $filepath !== false && file_exists($filepath)) {
            unlink($filepath);
        }
    }

    /** @param array<mixed> $params */
    public function addStreamFilter(string $filtername, array $params = []): self
    {
        $this->stream->addWriteFilter($filtername, $params);

        return $this;
    }

    public function getMessageCount(): int
    {
        return $this->counter->messageCount();
    }

    public function writeSegments(Segment ...$segments): void
    {
        if ($this->buildIsFinalized) {
            throw BuildException::finalizedBuildNotModifiable();
        }

        foreach ($segments as $segment) {
            $segmentName = $segment->name();

            if ($segmentName === 'UNB') {
                $this->unbRef = $segment->getValueFromPosition(5, 0) ?? '';
            }

            if ($segmentName === 'UNH') {
                if ($this->unhRef !== null) {
                    $this->writeUntSegment();
                }

                $this->unhRef = $segment->getValueFromPosition(1, 0) ?? '';
            }

            $this->writeSegment($segment);
        }
    }

    public function get(): Stream
    {
        if (!$this->messageIsEmpty()) {
            if ($this->unhRef !== null) {
                $this->writeUntSegment();
                $this->unhRef = null;
            }

            if ($this->unbRef !== null) {
                $this->writeSegment(GenericSegment::fromAttributes(
                    'UNZ',
                    [(string) $this->counter->messageCount()],
                    [$this->unbRef],
                ));
                $this->unbRef = null;
            }
        }

        $this->flushWriteBuffer();

        $this->buildIsFinalized = true;

        return $this->stream;
    }

    public function messageIsEmpty(): bool
    {
        return $this->writeBuffer === '' && $this->stream->isEmpty();
    }

    private function writeUntSegment(): void
    {
        $unhCount = $this->counter->unhCount() + 1;

        $this->writeSegment(GenericSegment::fromAttributes('UNT', [(string) $unhCount], [(string) $this->unhRef]));
    }

    private function writeSegment(Segment $segment): void
    {
        $this->writeBuffer .= $this->stringFormatter->format($segment);

        if (strlen($this->writeBuffer) >= self::WRITE_BUFFER_SIZE) {
            $this->flushWriteBuffer();
        }

        $this->counter->count($segment);
    }

    private function flushWriteBuffer(): void
    {
        if ($this->writeBuffer === '') {
            return;
        }

        $this->stream->write($this->writeBuffer);
        $this->writeBuffer = '';
    }
}
