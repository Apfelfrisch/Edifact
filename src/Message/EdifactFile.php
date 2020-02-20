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
    private $filename;
    private $openMode;
    private $ressource;
    private $delimiter;
    private $userIncludePath;
    private $readFilter = [];
    private $writeFilter = [];

    public function __construct($filename, $openMode = 'r', $userIncludePath = false)
    {
        if (is_string($filename) && empty($filename)) {
            throw new RuntimeException(__METHOD__ . "({$filename}): Filename cannot be empty");
        }
        if (!is_string($openMode)) {
            throw new Exception('EdifactFile::__construct() expects parameter 2 to be string, ' . gettype($openMode) . ' given');
        }

        parent::__construct($filename);

        $this->filename = $filename;
        $this->openMode = $openMode;
        $this->userIncludePath = $userIncludePath;

        $this->setRessource();
    }

    public function __destruct()
    {
        $this->close();
    }

    public static function fromString($string, $filename = 'php://temp', $writeFilter = [])
    {

        $instance = new self($filename, 'w+');

        foreach ($writeFilter as $callable) {
            $instance->addWriteFilter($callable);
        }

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

    public function addReadFilter(Callable $filter)
    {
        $id = spl_object_hash($filter);
        if (!isset($this->readFilter[$id])) {
            $this->readFilter[$id] = $filter;
        }
    }

    public function addWriteFilter(Callable $filter)
    {
        $id = spl_object_hash($filter);
        if (!isset($this->writeFilter[$id])) {
            $this->writeFilter[$id] = $filter;
        }
    }

    public function getContents()
    {
        return $this->applyReadFilter(trim(stream_get_contents($this->getRessource())));
    }

    public function close()
    {
        if (null !== $this->ressource) {
            fclose($this->ressource);
            $this->ressource = null;
        }
    }

    public function eof()
    {
        return feof($this->getRessource());
    }

    public function flush()
    {
        return fflush($this->getRessource());
    }

    public function getChar()
    {
        return $this->applyReadFilter(fgetc($this->getRessource()));
    }

    public function getSegment()
    {
        return $this->applyReadFilter($this->fetchSegment());
    }

    public function lock($operation, &$wouldblock = false)
    {
        return flock($this->getRessource(), $operation, $wouldblock);
    }

    public function passthru()
    {
        return fpassthru($this->getRessource());
    }

    public function read($length)
    {
        return $this->applyReadFilter(fread($this->getRessource(), $length));
    }

    public function seek($offset, $whence = SEEK_SET)
    {
        if (0 == $result = fseek($this->getRessource(), $offset, $whence)) {
            return true;
        }
        return false;
    }

    public function stat()
    {
        return fstat($this->getRessource());
    }

    public function tell()
    {
        return ftell($this->getRessource());
    }

    public function write($str)
    {
        fwrite($this->getRessource(), $this->applyWriteFilter($str));
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
        rewind($this->getRessource());
    }

    private function applyReadFilter($content)
    {
        foreach ($this->readFilter as $filter) {
            $content = $filter($content);
        }
        return $content;
    }

    private function applyWriteFilter($content)
    {
        foreach ($this->writeFilter as $filter) {
            $content = $filter($content);
        }
        return $content;
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
        // stream_get_line doesnt Return the rest of the string when the last char is not the
        // Delimiter and a read filter is appended. To avoid this Problem we have to check if
        // the stream is on its and, if not simply return the rest of the string with fread

        if ($this->eof()) {
            return false;
        }

        $position = $this->tell();

        if (false === $line = stream_get_line($this->getRessource(), 0, $this->getDelimiter()->getSegment())) {
            $this->seek($position);
            $line = $this->read(1024);
        }

        return $line;
    }

    private function delimiterWasTerminated($line)
    {
        return $line[(strlen($line) - 1)] == $this->getDelimiter()->getTerminator();
    }

    private function setRessource()
    {
        $this->ressource = @fopen($this->filename, $this->openMode, $this->userIncludePath);
        if (false === $this->ressource) {
            throw new RuntimeException(__METHOD__ . "({$this->filename}): failed to open stream: No such file or directory");
        }
    }

    private function getRessource()
    {
        if ($this->ressource === null) {
            $this->setRessource();
        }
        return $this->ressource;
    }
}

