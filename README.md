# PHP - Edifact

![Unit Test](https://github.com/Apfelfrisch/Edifact/actions/workflows/phpunit.yml/badge.svg)
![Static Analysis](https://github.com/Apfelfrisch/Edifact/actions/workflows/psalm.yml/badge.svg)

A PHP library, wich provides a Framework to parse, build, serialize and validate UN/EDIFACT messages.

Highlights
-------
* Parse and Write Files in a memory efficient and scalable way 
* Parse each Segment to its specific Object, this way we can define getter, setter and validation it

Usage
-------

Parse an Edifact Message:

```php
use Proengeno\Edifact\Message;
use Proengeno\Edifact\Segments\Nad;

$message = Message::fromString("UNA:+.? 'NAD+DP++++Musterstr.::10+City++12345+DE");

foreach ($message as $segment) {
    if ($segment instanceof Nad) {
        echo $segment->street(); // Musterstr.
    }
}
```

Build an Edifact Message:

The Builder takes Segments wich implements the SegInterface, trailing Segments (UNT and UNZ) will automatically be added. 
If no UNA Segement is provided, it uses the default values (UNA:+.? )

```php
use Proengeno\Edifact\Builder;

$builder = new Builder;

$builder->writeSegments(
    Unb::fromAttributes('1', '2', 'sender', '500', 'receiver', '400', new DateTime('2021-01-01 12:01:01'), 'unb-ref'),
    Unh::fromAttributes('unh-ref', 'type', 'v-no', 'r-no', 'o-no', 'o-co')
);

$message = $builder->get();
```
