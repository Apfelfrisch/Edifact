<?php

namespace Proengeno\Edifact\Message;

use SplFileInfo;
use RuntimeException;
use Proengeno\Edifact\Message\Delimiter;
use Throwable;

final class EdifactFile extends SplFileInfo
{
    /* @var array<string, array<resource>> */
    private array $filters = [];

    /** @var resource */
    private $resource;

    private Delimiter $delimiter;

    public function __construct(
        string $filename,
        string $openMode = 'r',
        ?Delimiter $delimiter = null
    ) {
        parent::__construct($filename);

        $resource = null;
        try {
            $resource = fopen($filename, $openMode);
        } catch (Throwable) { }

        if (! is_resource($resource)) {
            throw new RuntimeException(__METHOD__ . "({$filename}): failed to open stream: No such file or directory");
        }

        $this->resource = $resource;

        if ($openMode === 'r' || substr($openMode, 1, 1) === '+') { // Stream is readable
            $this->delimiter = $delimiter ?? Delimiter::setFromFile($this);
        } else {
            $this->delimiter = $delimiter ?? new Delimiter;
        }
    }

    /**
     * @param list<string> $writeFilter
     */
    public static function fromString(string $string, string $filename = 'php://temp', array $writeFilter = []): self
    {
        $instance = new self($filename, 'w+', Delimiter::setFromString($string));

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

    public function getContents(): string
    {
        return trim(stream_get_contents($this->resource));
    }

    public function eof(): bool
    {
        return feof($this->resource);
    }

    public function flush(): bool
    {
        return fflush($this->resource);
    }

    public function getChar(): string
    {
        return fgetc($this->resource);
    }

    public function getSegment(): string
    {
        return $this->fetchSegment();
    }

    public function lock(int $operation): bool
    {
        return flock($this->resource, $operation);
    }

    public function passthru(): int|bool
    {
        return fpassthru($this->resource);
    }

    public function read(int $length): string
    {
        return fread($this->resource, $length);
    }

    public function seek(int $offset, int $whence = SEEK_SET): bool
    {
        return 0 == fseek($this->resource, $offset, $whence);
    }

    public function stat(): array
    {
        return fstat($this->resource);
    }

    public function tell(): int
    {
        return (int)ftell($this->resource);
    }

    public function write(string $str): int|false
    {
        return fwrite($this->resource, $str);
    }

    public function writeAndRewind(string $str): void
    {
        $this->write($str);
        $this->rewind();
    }

    public function getDelimiter(): Delimiter
    {
        return $this->delimiter;
    }

    public function rewind(): void
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
            if (! $this->delimiterWasTerminated($line)) {
                return $mergedLines . $line;
            }

            $mergedLines .= substr_replace($line, $this->getDelimiter()->getSegment(), -1);
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

