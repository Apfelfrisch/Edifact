<?php

namespace Proengeno\Edifact\Message;

use Exception;
use SplFileInfo;
use LogicException;
use DomainException;
use RuntimeException;
use SeekableIterator;
use RecursiveIterator;
use Proengeno\Edifact\Message\Delimiter;

class EdifactFile extends SplFileInfo implements RecursiveIterator, SeekableIterator 
{
    private $rsrc;
    private $filename;
    private $delimiter;
    private $maxLineLen = 0;
    private $currentSegment = false;
    private $currentSegmentNumber = 0;
    
    public function __construct($filename, $open_mode = 'r', $use_include_path = false) 
    {
        if (is_string($filename) && empty($filename)) {
            throw new RuntimeException(__METHOD__ . "({$filename}): Filename cannot be empty");
        }
        if (!is_string($open_mode)) {
            throw new Exception('SplFileObject::__construct() expects parameter 2 to be string, ' . gettype($open_mode) . ' given');
        }

        parent::__construct($filename);
        $this->filename = $filename;
        $this->rsrc = @fopen($filename, $open_mode, $use_include_path);
        if (false === $this->rsrc) {
            throw new RuntimeException(__METHOD__ . "({$filename}): failed to open stream: No such file or directory");
        }
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
        if (false === $result = stream_get_contents($this->rsrc)) {
            throw new RuntimeException('Error reading from stream');
        }
        return trim($result);
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
        $char = fgetc($this->rsrc);
        if ($char == "'") {
            if ($this->tell() < 10) {
                $this->currentSegmentNumber++;
                return $char;
            }
            $this->seek($this->tell() - 2);
            $preChar = fgetc($this->rsrc);
            if ($preChar != $this->getDelimiter()->getTerminator() && $preChar != $this->getDelimiter()->getEmpty()) {
                $this->currentSegmentNumber++;
            }
            $this->seek($this->tell() + 1);
        }
        return $char;
    }
    
    public function getSegment() 
    {
        if (false !== $this->currentSegment) {
            $this->next();
        }
        
        return $this->currentSegment = $this->fetchSegment();
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
    
    public function truncate($size) 
    {
        return ftruncate($this->rsrc, $size);
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

    public function getMaxLineLen() 
    {
        return $this->maxLineLen;
    }

    public function getDelimiter()
    {
        if ($this->delimiter === null) {
            $this->delimiter = Delimiter::setFromFile($this);
        }
        return $this->delimiter;
    }
    
    public function setMaxLineLen($max_len) 
    {
        if ($max_len < 0) {
            throw new DomainException('Maximum line length must be greater than or equal zero');
        }
        $this->maxLineLen = $max_len;
    }

    public function rewind() 
    {
        rewind($this->rsrc);
        $this->currentSegmentNumber = 0;
        $this->currentSegment = false;
    }
    
    public function seekToSegment($segmentPosition) 
    {
        if ($segmentPosition < 0) {
            throw new LogicException("Can't seek file " . $this->filename . " to negative Segment position $segmentPosition");
        }
        $this->rewind();
        for ($i = 0; $i < $segmentPosition; $i++) {
            $this->current();
            $this->next();
            if ($this->eof()) {
                $this->currentSegmentNumber--;
                break;
            }
        }
        $this->current();
    }
    
    /*
     * Needed for RecursiveIterator Interface
     */
    public function current() 
    {
        if ($this->currentSegment === false) {
            $this->currentSegment = $this->fetchSegment();
        }
        return $this->currentSegment;
    }
    
    /*
     * Needed for RecursiveIterator Interface
     */
    public function key() 
    {
        return $this->currentSegmentNumber;
    }
    
    /*
     * Needed for RecursiveIterator Interface
     */
    public function next() 
    {
        $this->currentSegment = false;
        $this->currentSegmentNumber++;
    }
    
    /*
     * Needed for RecursiveIterator Interface
     */
    public function valid() 
    {
        return $this->current() !== false;
    }

    /*
     * Needed for RecursiveIterator Interface
     */
    public function getChildren() 
    {
        return null;
    }

    /*
     * Needed for RecursiveIterator Interface
     */
    public function hasChildren() 
    {
        return false;
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

        return $line;
    }

    private function streamGetLine()
    {
        return stream_get_line($this->rsrc, $this->maxLineLen, $this->getDelimiter()->getSegment());
    }
    
    private function delimiterWasTerminated($line)
    {
        return $line[(strlen($line) - 1)] == $this->getDelimiter()->getTerminator();
    }
}
