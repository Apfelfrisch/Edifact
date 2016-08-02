<?php

namespace Proengeno\Edifact\Exceptions;

class ValidationException extends EdifactException
{
    protected $lineCount;
    protected $segment;
    
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
        $message .= ", " . $postMessage;
        $this->lineCount = $lineCount;
        $this->segment = $segment;

        parent::__construct($message);
    }
    
    public static function unexpectedSegment($lineCount, $segment, $expectedSegment = null)
    {
        if ($expectedSegment) {
            $message = "Segment unexpected, $expectedSegment expected.";
        } else {
            $message = "Segment unexpected, End expected.";
        }

        return new static($message, $lineCount, $segment);
    }

    public static function illegalContent($lineCount, $segment, $illegalContent, $legalContent)
    {
        $message = "Illegal Content '$illegalContent', '$legalContent' allowed.";
        return new static($message, $lineCount, $segment);
    }

    public static function maxLoopsExceeded($lineCount, $segment)
    {
        return new static('Maximal Loops exceeded.', $lineCount, $segment);
    }

    public static function unexpectedEnd()
    {
        return new static('Unexpected End.', null, null);
    }
}
