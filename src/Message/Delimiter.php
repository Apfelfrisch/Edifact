<?php

namespace Proengeno\Edifact\Message;

final class Delimiter
{
    const UNA_SEGMENT = 'UNA';
    const PLACE_HOLDER = '«>~<«';

    public function __construct(
        private string $data = ':',
        private string $dataGroup = '+',
        private string $decimal = '.',
        private string $terminator = '?',
        private string $empty = ' ',
        private string $segment = '\''
    ) { }

    public static function setFromFile(EdifactFile $file, ?self $fallback = null): self
    {
        $position = $file->tell();
        $file->rewind();

        $instance = self::setFromString($file->read(9), $fallback);

        $file->seek($position);

        return $instance;
    }

    public static function setFromString(string $string, ?self $fallback = null): self
    {
        if (substr($string, 0, 3) !== self::UNA_SEGMENT) {
            return $fallback ?? new self();
        }

        if (! isset($string[8])) {
            return $fallback ?? new self();
        }

        return new self(
            $string[3], $string[4], $string[5], $string[6], $string[7], $string[8]
        );
    }

    public function terminate(string $string): string
    {
        return str_replace(
            [$this->data, $this->dataGroup, '\\n'],
            [$this->terminator . $this->data, $this->terminator . $this->dataGroup, ''],
            $string
        );
    }

    /** @return list<string> */
    public function explodeSegments(string $string): array
    {
        return $this->explodeString($string, $this->dataGroup);
    }

    /** @return list<string> */
    public function explodeElements(string $string): array
    {
        return $this->explodeString($string, $this->data);
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function getDataGroup(): string
    {
        return $this->dataGroup;
    }

    public function getDecimal(): string
    {
        return $this->decimal;
    }

    public function getTerminator(): string
    {
        return $this->terminator;
    }

    public function getEmpty(): string
    {
        return $this->empty;
    }

    public function getSegment(): string
    {
        return $this->segment;
    }

    /**
     * @return list<string>
     */
    private function explodeString(string $string, string $pattern): array
    {
        $string = str_replace(["\r", "\n"], '', $string);

        if ($foundTermination = (boolean)strpos($string, $this->terminator . $pattern)) {
            $string = str_replace($this->terminator . $pattern, self::PLACE_HOLDER, $string);
        }

        $explodedString = explode($pattern, $string);

        if ($foundTermination) {
            for ($i = 0, $count = count($explodedString); $i < $count; $i++) {
                $explodedString[$i] = str_replace(self::PLACE_HOLDER, $pattern, $explodedString[$i]);
            }
        }

        return $this->trimLastItem($explodedString);
    }

    /**
     * @param list<string> $array
     *
     * @return list<string>
     */
    private function trimLastItem(array $array): array
    {
        if (end($array) == '') {
            array_pop($array);
        }

        return $array;
    }
}
