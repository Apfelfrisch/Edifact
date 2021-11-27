<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Exceptions;

class ValidationException extends EdifactException
{
    /** @var int|null */
    protected $lineCount;

    /** @var string|null */
    protected $segment;

    /**
     * @param string $postMessage
     * @param int|null $lineCount
     * @param string|null $segment
     *
     * @return static
     *
     * @psalm-suppress UnsafeInstantiation
     */
    public function __construct($postMessage, $lineCount = null, $segment = null)
    {
        $message = '';
        if ($lineCount) {
            $message = "Line $lineCount";
        }
        if ($lineCount && $segment) {
            $message .= ",";
        }
        if ($segment) {
            $message .= " Segment $segment";
        }
        if ($message != '') {
            $message .= ", ";
        }
        $message .= $postMessage;
        $this->lineCount = $lineCount;
        $this->segment = $segment;

        parent::__construct($message);
    }

    /**
     * @param int|null $lineCount
     * @param string|null $segment
     * @param string|null $expectedSegment
     *
     * @return static
     *
     * @psalm-suppress UnsafeInstantiation
     */
    public static function unexpectedSegment($lineCount, $segment, $expectedSegment = null)
    {
        if ($expectedSegment) {
            $message = "Segment unexpected, $expectedSegment expected.";
        } else {
            $message = "Segment unexpected, End expected.";
        }

        return new static($message, $lineCount, $segment);
    }

    /**
     * @param int|null $lineCount
     * @param string|null $segment
     * @param string $illegalContent
     * @param string $legalContent
     *
     * @return static
     *
     * @psalm-suppress UnsafeInstantiation
     */
    public static function illegalContent($lineCount, $segment, $illegalContent, $legalContent)
    {
        $message = "Illegal Content '$illegalContent', '$legalContent' allowed.";
        return new static($message, $lineCount, $segment);
    }

    /**
     * @param int $lineCount
     * @param string $segment
     *
     * @return static
     *
     * @psalm-suppress UnsafeInstantiation
     */
    public static function maxLoopsExceeded($lineCount, $segment)
    {
        return new static('Maximal Loops exceeded.', $lineCount, $segment);
    }

    /**
     * @return static
     *
     * @psalm-suppress UnsafeInstantiation
     */
    public static function unexpectedEnd()
    {
        return new static('Unexpected End.', null, null);
    }
}
