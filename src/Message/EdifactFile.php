<?php

namespace Proengeno\Edifact\Message;

use SplFileInfo;
use RuntimeException;
use Proengeno\Edifact\Message\Delimiter;
use Throwable;

final class EdifactFile extends SplFileInfo
{
    /*
     * Attached filters.
     *
     * @var array<string, array<resource>>
     */
    private array $filters = [];

    /** @var resource */
    private $resource;

    private ?Delimiter $delimiter = null;

    public function __construct(
        private string $filename,
        private string $openMode = 'r',
        private bool $userIncludePath = false
    ) {
        parent::__construct($filename);

        $resource = null;
        try {
            $resource = fopen($this->filename, $this->openMode, $this->userIncludePath);
        } catch (Throwable) { }

        if (! is_resource($resource)) {
            throw new RuntimeException(__METHOD__ . "({$this->filename}): failed to open stream: No such file or directory");
        }

        $this->resource = $resource;
    }

    /**
     * @param string $string
     * @param string $filename
     * @param list<string> $writeFilter
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

    public function __destruct()
    {
        array_walk_recursive($this->filters, static function (mixed $filter): bool {
            return @stream_filter_remove($filter);
        });

        fclose($this->resource);

        unset($this->resource);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        try {
            $this->rewind();

            return $this->getContents();
        } catch (RuntimeException) {
            return '';
        }
    }

    public function addReadFilter(string $filter, mixed $params = null): self
    {
        $this->addFilter($filter, STREAM_FILTER_READ, $params);

        return $this;
    }

    public function addWriteFilter(string $filter, mixed $params = null): self
    {
        $this->addFilter($filter, STREAM_FILTER_WRITE, $params);

        return $this;
    }

    /**
     * @return string
     */
    public function getContents()
    {
        return trim(stream_get_contents($this->resource));
    }

    /**
     * @return bool
     */
    public function eof()
    {
        return feof($this->resource);
    }

    /**
     * @return bool
     */
    public function flush()
    {
        return fflush($this->resource);
    }

    /**
     * @return string
     */
    public function getChar()
    {
        return fgetc($this->resource);
    }

    /**
     * @return string
     */
    public function getSegment()
    {
        return $this->fetchSegment();
    }

    /**
     * @param int $operation
     *
     * @return bool
     */
    public function lock($operation)
    {
        return flock($this->resource, $operation);
    }

    /**
     * @return bool|int
     */
    public function passthru()
    {
        return fpassthru($this->resource);
    }

    /**
     * @param int $length
     *
     * @return string
     */
    public function read($length)
    {
        return fread($this->resource, $length);
    }

    /**
     * @param int $offset
     * @param int $whence
     *
     * @return bool
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        return 0 == fseek($this->resource, $offset, $whence);
    }

    /**
     * @return array
     */
    public function stat()
    {
        return fstat($this->resource);
    }

    /**
     * @return int
     */
    public function tell()
    {
        return (int)ftell($this->resource);
    }

    /**
     * @param string $str
     *
     * @return int|false
     */
    public function write($str)
    {
        return fwrite($this->resource, $str);
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
        rewind($this->resource);
    }

    private function addFilter(string $filtername, int $direction, mixed $params = null): void
    {
        $res = @stream_filter_append($this->resource, $filtername, $direction, $params);

        if (! is_resource($res)) {
            throw new RuntimeException('unable to locate filter `'.$filtername.'`');
        }

        $this->filters[$filtername][] = $res;
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
        return stream_get_line($this->resource, 0, $this->getDelimiter()->getSegment());
    }

    private function delimiterWasTerminated(string $line): bool
    {
        return str_ends_with($line, $this->getDelimiter()->getTerminator());
    }
}
