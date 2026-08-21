<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Iterators\Stream;

use Apfelfrisch\Edifact\Exceptions\InvalidStreamException;
use Apfelfrisch\Edifact\Segment\UnaSegment;
use SplFileInfo;
use Throwable;

final class Stream extends SplFileInfo
{
    /** @var array<string, list<resource>> */
    private array $filters = [];

    /** @var resource */
    private $resource;

    private readonly bool $readable;

    private readonly UnaSegment $unaSegment;

    public function __construct(string $filename, string $openMode = 'r', ?UnaSegment $unaSegment = null)
    {
        parent::__construct($filename);

        set_error_handler(static fn(): bool => true);

        try {
            $resource = fopen($filename, $openMode);
        } catch (Throwable) {
            $resource = false;
        } finally {
            restore_error_handler();
        }

        if (!is_resource($resource)) {
            throw InvalidStreamException::readError($filename);
        }

        $this->resource = $resource;
        $this->readable = $openMode === 'r' || str_contains($openMode, '+');

        $this->unaSegment = $this->readable
            ? UnaSegment::setFromStream($this, fallback: $unaSegment)
            : $unaSegment ?? new UnaSegment();
    }

    /**
     * @param list<string> $writeFilter
     */
    public static function fromString(string $string, string $filename = 'php://temp', array $writeFilter = []): self
    {
        // The UnaSegment has to be set here, because the stream is empty on instantiation
        $instance = new self($filename, 'w+', UnaSegment::setFromString($string));

        foreach ($writeFilter as $filter) {
            $instance->addWriteFilter($filter);
        }

        $instance->writeAndRewind($string);

        return $instance;
    }

    public function __destruct()
    {
        foreach ($this->filters as $appliedFilters) {
            foreach ($appliedFilters as $appliedFilter) {
                stream_filter_remove($appliedFilter);
            }
        }

        fclose($this->resource);

        unset($this->resource);
    }

    public function toString(): string
    {
        if (!$this->readable) {
            return '';
        }

        $this->rewind();

        return $this->getContents();
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    /** @param array<mixed> $params */
    public function addReadFilter(string $filter, array $params = []): self
    {
        $this->addFilter($filter, STREAM_FILTER_READ, $params);

        return $this;
    }

    /** @param array<mixed> $params */
    public function addWriteFilter(string $filter, array $params = []): self
    {
        $this->addFilter($filter, STREAM_FILTER_WRITE, $params);

        return $this;
    }

    public function getContents(): string
    {
        $contents = stream_get_contents($this->resource);

        return $contents === false ? '' : trim($contents);
    }

    public function eof(): bool
    {
        return feof($this->resource);
    }

    public function flush(): bool
    {
        return fflush($this->resource);
    }

    public function getChar(): string|false
    {
        return fgetc($this->resource);
    }

    public function getSegment(): string
    {
        $mergedLines = '';

        while (($line = $this->streamGetLine()) !== false && $line !== '' && !ctype_cntrl($line)) {
            if (!$this->terminatorWasEscaped($line)) {
                return $mergedLines . $line;
            }

            $mergedLines .= substr_replace($line, $this->unaSegment->segmentTerminator(), -1);
        }

        return $mergedLines;
    }

    public function lock(int $operation): bool
    {
        return flock($this->resource, $operation);
    }

    public function passthru(): int|bool
    {
        return fpassthru($this->resource);
    }

    /** @param int<0, max> $length */
    public function read(int $length): string|false
    {
        return fread($this->resource, $length);
    }

    public function seek(int $offset, int $whence = SEEK_SET): bool
    {
        return fseek($this->resource, $offset, $whence) === 0;
    }

    /** @return array<int|string, int>|false */
    public function stat(): array|false
    {
        return fstat($this->resource);
    }

    public function tell(): int
    {
        return (int) ftell($this->resource);
    }

    public function isEmpty(): bool
    {
        $tell = $this->tell();

        $this->seek(0, SEEK_END);

        $result = $this->tell() === 0;

        $this->seek($tell);

        return $result;
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

    public function getUnaSegment(): UnaSegment
    {
        return $this->unaSegment;
    }

    public function rewind(): void
    {
        rewind($this->resource);
    }

    /** @param array<mixed> $params */
    private function addFilter(string $filtername, int $direction, array $params): void
    {
        // @mago-expect lint:no-error-control-operator
        $res = @stream_filter_append($this->resource, $filtername, $direction, $params);

        if (!is_resource($res)) {
            throw InvalidStreamException::filterError($filtername);
        }

        $this->filters[$filtername][] = $res;
    }

    private function streamGetLine(): string|false
    {
        return stream_get_line($this->resource, 0, $this->unaSegment->segmentTerminator());
    }

    private function terminatorWasEscaped(string $line): bool
    {
        return str_ends_with($line, $this->unaSegment->escapeCharacter());
    }
}
