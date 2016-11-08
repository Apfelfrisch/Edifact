<?php

namespace Proengeno\Edifact\Message;

use Iterator;
use Exception;
use SplFileInfo;
use LogicException;
use DomainException;
use RuntimeException;
use Proengeno\Edifact\Message\Delimiter;

class EdifactFile extends SplFileInfo
{
    private $rsrc;
    private $filename;
    private $delimiter;

    public function __construct($filename, $open_mode = 'r', $use_include_path = false)
    {
        if (is_string($filename) && empty($filename)) {
            throw new RuntimeException(__METHOD__ . "({$filename}): Filename cannot be empty");
        }
        if (!is_string($open_mode)) {
            throw new Exception('EdifactFile::__construct() expects parameter 2 to be string, ' . gettype($open_mode) . ' given');
        }

        parent::__construct($filename);
        $this->filename = $filename;
        $this->rsrc = @fopen($filename, $open_mode, $use_include_path);
        if (false === $this->rsrc) {
            throw new RuntimeException(__METHOD__ . "({$filename}): failed to open stream: No such file or directory");
        }
    }

    public function fromString($string)
    {
        $instance = new self('php://temp', 'w+');
        $instance->writeAndRewind($string);

        return $instance;
    }

    public function __toString()
    {
        try {
            $this->rewind();
            return $this->getContents();
        } catch (RuntimeException $e) {
            return '';
        }
    }

    public function getContents()
    {
        return trim(stream_get_contents($this->rsrc));
    }

    public function eof()
    {
        return feof($this->rsrc);
    }

    public function flush()
    {
        return fflush($this->rsrc);
    }

    public function getChar()
    {
        return fgetc($this->rsrc);
    }

    public function getSegment()
    {
        return $this->fetchSegment();
    }

    public function lock($operation, &$wouldblock = false)
    {
        return flock($this->rsrc, $operation, $wouldblock);
    }

    public function passthru()
    {
        return fpassthru($this->rsrc);
    }

    public function read($length)
    {
        return fread($this->rsrc, $length);
    }

    public function seek($offset, $whence = SEEK_SET)
    {
        if (0 == $result = fseek($this->rsrc, $offset, $whence)) {
            return true;
        }
        return false;
    }

    public function stat()
    {
        return fstat($this->rsrc);
    }

    public function tell()
    {
        return ftell($this->rsrc);
    }

    public function write($str)
    {
        fwrite($this->rsrc, $str);
    }

    public function writeAndRewind($str)
    {
        $this->write($str);
        $this->rewind();
    }

    public function getDelimiter()
    {
        if ($this->delimiter === null) {
            $this->delimiter = Delimiter::setFromFile($this);
        }
        return $this->delimiter;
    }

    public function rewind()
    {
        rewind($this->rsrc);
    }

    private function fetchSegment()
    {
        $mergedLines = '';
        while ($line = $this->streamGetLine()) {
            // Skip empty Segments
            if (ctype_cntrl($line) || empty($line)) {
                continue;
            }
            if ($this->delimiterWasTerminated($line)) {
                $line[(strlen($line) - 1)] = $this->getDelimiter()->getSegment();
                $mergedLines .= $line;
                continue;
            }

            return $mergedLines . $line;
        }

        return $mergedLines;
    }

    private function streamGetLine()
    {
        return stream_get_line($this->rsrc, 0, $this->getDelimiter()->getSegment());
    }

    private function delimiterWasTerminated($line)
    {
        return $line[(strlen($line) - 1)] == $this->getDelimiter()->getTerminator();
    }
}

