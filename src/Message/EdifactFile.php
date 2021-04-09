<?php

namespace Proengeno\Edifact\Message;

use Iterator;
use Exception;
use SplFileInfo;
use LogicException;
use DomainException;
use RuntimeException;
use Proengeno\Edifact\Message\Delimiter;
use Throwable;

class EdifactFile extends SplFileInfo
{
    /** @var array<string, Callable> */
    private array $readFilter = [];

    /** @var array<string, Callable> */
    private array $writeFilter = [];

    /** @var mixed */
    private $ressource;

    private ?Delimiter $delimiter = null;

    public function __construct(
        private string $filename,
        private string $openMode = 'r',
        private bool $userIncludePath = false
    ) {
        parent::__construct($filename);

        $ressource = null;
        try {
            $ressource = fopen($this->filename, $this->openMode, $this->userIncludePath);
        } catch (Throwable) { }

        if (! is_resource($ressource)) {
            throw new RuntimeException(__METHOD__ . "({$this->filename}): failed to open stream: No such file or directory");
        }

        $this->ressource = $ressource;
    }

    /**
     * @param string $string
     * @param string $filename
     * @param list<Callable> $writeFilter
     *
     * @return self
     */
    public static function fromString($string, $filename = 'php://temp', $writeFilter = [])
    {
        $instance = new self($filename, 'w+');

        foreach ($writeFilter as $callable) {
            $instance->addWriteFilter($callable);
        }

        $instance->writeAndRewind($string);

        return $instance;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        try {
            $this->rewind();

            return $this->getContents();
        } catch (RuntimeException $e) {
            return '';
        }
    }

    /**
     * @return void
     */
    public function addReadFilter(Callable $filter)
    {
        $id = spl_object_hash((object)$filter);
        if (!isset($this->readFilter[$id])) {
            $this->readFilter[$id] = $filter;
        }
    }

    /**
     * @return void
     */
    public function addWriteFilter(Callable $filter)
    {
        $id = spl_object_hash((object)$filter);
        if (!isset($this->writeFilter[$id])) {
            $this->writeFilter[$id] = $filter;
        }
    }

    /**
     * @return string
     */
    public function getContents()
    {
        return $this->applyReadFilter(trim(stream_get_contents($this->ressource)));
    }

    /**
     * @return bool
     */
    public function eof()
    {
        return feof($this->ressource);
    }

    /**
     * @return bool
     */
    public function flush()
    {
        return fflush($this->ressource);
    }

    /**
     * @return string
     */
    public function getChar()
    {
        return $this->applyReadFilter(fgetc($this->ressource));
    }

    /**
     * @return string
     */
    public function getSegment()
    {
        return $this->applyReadFilter($this->fetchSegment());
    }

    /**
     * @param int $operation
     *
     * @return bool
     */
    public function lock($operation)
    {
        return flock($this->ressource, $operation);
    }

    /**
     * @return bool|int
     */
    public function passthru()
    {
        return fpassthru($this->ressource);
    }

    /**
     * @param int $length
     *
     * @return string
     */
    public function read($length)
    {
        return $this->applyReadFilter(fread($this->ressource, $length));
    }

    /**
     * @param int $offset
     * @param int $whence
     *
     * @return bool
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        if (0 == $result = fseek($this->ressource, $offset, $whence)) {
            return true;
        }
        return false;
    }

    /**
     * @return array
     */
    public function stat()
    {
        return fstat($this->ressource);
    }

    /**
     * @return int
     */
    public function tell()
    {
        return (int)ftell($this->ressource);
    }

    /**
     * @param string $str
     *
     * @return int|false
     */
    public function write($str)
    {
        return fwrite($this->ressource, $this->applyWriteFilter($str));
    }

    /**
     * @param string $str
     *
     * @return void
     */
    public function writeAndRewind($str)
    {
        $this->write($str);
        $this->rewind();
    }

    /**
     * @return Delimiter
     */
    public function getDelimiter()
    {
        if ($this->delimiter === null) {
            $this->delimiter = Delimiter::setFromFile($this);
        }
        return $this->delimiter;
    }

    /**
     * @return void
     */
    public function rewind()
    {
        rewind($this->ressource);
    }

    private function applyReadFilter(string $content): string
    {
        foreach ($this->readFilter as $filter) {
            $content = $filter($content);
        }
        return $content;
    }

    private function applyWriteFilter(string $content): string
    {
        foreach ($this->writeFilter as $filter) {
            $content = $filter($content);
        }
        return $content;
    }


    private function fetchSegment(): string
    {
        $mergedLines = '';
        while (($line = $this->streamGetLine()) && !ctype_cntrl($line)) {
            if ($this->delimiterWasTerminated($line)) {
                $line[(strlen($line) - 1)] = $this->getDelimiter()->getSegment();
                $mergedLines .= $line;
                continue;
            }

            return $mergedLines . $line;
        }

        return $mergedLines;
    }

    private function streamGetLine(): string|false
    {
        // stream_get_line doesnt Return the rest of the string when the last char is not the
        // Delimiter and a read filter is appended. To avoid this Problem we have to check if
        // the stream is on its and, if not simply return the rest of the string with fread

        if ($this->eof()) {
            return false;
        }

        $position = $this->tell();

        if (false === $line = stream_get_line($this->ressource, 0, $this->getDelimiter()->getSegment())) {
            $this->seek($position);
            $line = $this->read(1024);
        }

        return $line;
    }

    private function delimiterWasTerminated(string $line): bool
    {
        return str_ends_with($line, $this->getDelimiter()->getTerminator());
    }
}
