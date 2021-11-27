# PHP - Edifact

![Unit Test](https://github.com/Apfelfrisch/Edifact/actions/workflows/phpunit.yml/badge.svg)
![Static Analysis](https://github.com/Apfelfrisch/Edifact/actions/workflows/psalm.yml/badge.svg)

A PHP library, wich provides a Framework to parse, build, serialize and validate UN/EDIFACT messages.

## Highlights
* Parse and Write Files in a memory efficient and scalable way 
* Parse each Segment to its specific Object, this way we can define getter, setter and validation it

## Usage

### Parse Edifact Messages

#### Parse to the default Segments
```php
use Apfelfrisch\Edifact\Message;
use Apfelfrisch\Edifact\Segments\Nad;

$message = Message::fromString("UNA:+.? 'NAD+DP++++Musterstr.::10+City++12345+DE");

foreach ($message->getSegments() as $segment) {
    if ($segment instanceof Nad) {
        echo $segment->street(); // Musterstr.
    }
}
```

#### Filter Segments
```php
use Apfelfrisch\Edifact\Message;
use Apfelfrisch\Edifact\Segments\Nad;

$message = Message::fromString("UNA:+.? 'NAD+DP++++Musterstr.::10+City++12345+DE");

foreach ($message->filterSegments(Nad::class) as $segment) {
    echo $segment->name(); // NAD
}

$message->filterSegments(Nad::class, fn(Nad $seg): bool 
    => $seg->street() === 'Musterstr.'
);

echo $message->findFirstSegment(Nad::class)->name(); // NAD
```

#### Parse to the generic Segment
```php
use Apfelfrisch\Edifact\Message;
use Apfelfrisch\Edifact\Segments\Generic;

$segmentFactory = new SegmentFactory;
$segmentFactory->addFallback(Generic::class);

$message = Message::fromString("UNA:+.? 'NAD+DP++++Musterstr.::10+City++12345+DE", $segmentFactory);

foreach ($message->getSegments() as $segment) {
    if ($segment instanceof Generic) {
        echo $segment->name(); // UNA.
    }
}
```

#### Parse to your own Segments

```php
namespace My\Namespace;

use Apfelfrisch\Edifact\DataGroups;
use Apfelfrisch\Edifact\SegmentFactory;
use Apfelfrisch\Edifact\Segments\AbstractSegment;

class Seq extends AbstractSegment
{
    private static ?DataGroups $blueprint = null;

    public static function blueprint(): DataGroups
    {
        if (self::$blueprint === null) {
            self::$blueprint = (new DataGroups)
                ->addValue('SEQ', 'SEQ', 'M|a|3')
                ->addValue('1229', '1229', 'M|an|3');
        }

        return self::$blueprint;
    }

    public static function fromAttributes(string $code): self
    {
        return new self((new DataGroups)
            ->addValue('SEQ', 'SEQ', 'SEQ')
            ->addValue('1229', '1229', $code)
        );
    }

    public function code(): ?string
    {
        return $this->elements->getValue('1229', '1229');
    }
}

$segmentFactory = new SegmentFactory;
$segmentFactory->addSegment('SEQ', Seq::class);

$message = Message::fromString("UNA:+.? 'SEQ+1", $segmentFactory);
```

#### Unwrap Messages
```php
use Apfelfrisch\Edifact\Message;

$message = Message::fromString("UNA:+.? 'UNH+1+ORDERS:D:96A:UN'UNT+2+1'UNH+2+ORDERS:D:96A:UN'UNT+2+2'");

foreach ($message->unwrap() as $partialMessage) {
    echo $partialMessage::class // \Apfelfrisch\Edifact\Message
    echo $partialMessage->toString() // UNH+1+ORDERS:D:96A:UN'UNT+2+1', UNH+2+ORDERS:D:96A:UN'UNT+2+2'
}
```

#### Add Streamfilter to the Message
```php
use Apfelfrisch\Edifact\Message;

$message = Message::fromString("UNA:+.? 'NAD+DP++++Musterstr.::10+City++12345+DE");
$message->addStreamFilter('iso-to-utf8', 'convert.iconv.ISO-8859-1.UTF-8');
```

### Build an Edifact Message:

You can build Edifact Messages like so:

```php
use Apfelfrisch\Edifact\Builder;
use Apfelfrisch\Edifact\Message;
use Apfelfrisch\Edifact\Segments\Unb;
use Apfelfrisch\Edifact\Segments\Unh;

$builder = new Builder;

$builder->writeSegments(
    Unb::fromAttributes('1', '2', 'sender', '500', 'receiver', '400', new DateTime('2021-01-01 12:01:01'), 'unb-ref'),
    Unh::fromAttributes('unh-ref', 'type', 'v-no', 'r-no', 'o-no', 'o-co')
);

$message = new Message($builder->get());
```
Trailing Segments (UNT and UNZ) will be added automatically. 
If no UNA Segement is provided, it uses the default values (UNA:+.? )

#### Add Streamfilter to the Builder
```php
use Apfelfrisch\Edifact\Builder;
use Apfelfrisch\Edifact\Segments\Unb;

$builder = new Builder;
$builder->addStreamFilter('utf8-to-iso', 'convert.iconv.UTF-8.ISO-8859-1');

$builder->writeSegments(
    Unb::fromAttributes('1', '2', 'sender', '500', 'receiver', '400', new DateTime('2021-01-01 12:01:01'), 'unb-ref'),
    Unh::fromAttributes('unh-ref', 'type', 'v-no', 'r-no', 'o-no', 'o-co')
);
```
