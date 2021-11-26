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

foreach ($message as $segment) {
    if ($segment instanceof Nad) {
        echo $segment->street(); // Musterstr.
    }
}
```

#### Parse to the generic Segment
```php
use Apfelfrisch\Edifact\Message;
use Apfelfrisch\Edifact\Segments\Generic;

$segmentFactory = new SegmentFactory;
$segmentFactory->addFallback(Generic::class);

$message = Message::fromString("UNA:+.? 'NAD+DP++++Musterstr.::10+City++12345+DE", $segmentFactory);

foreach ($message as $segment) {
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
    private static ?DataGroups $validationBlueprint = null;

    public static function blueprint(): DataGroups
    {
        if (self::$validationBlueprint === null) {
            self::$validationBlueprint = (new DataGroups)
                ->addValue('SEQ', 'SEQ', 'M|a|3')
                ->addValue('1229', '1229', 'M|an|3');
        }

        return self::$validationBlueprint;
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
