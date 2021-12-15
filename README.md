# PHP - EDIFACT

![Unit Test](https://github.com/Apfelfrisch/Edifact/actions/workflows/phpunit.yml/badge.svg)
![Static Analysis](https://github.com/Apfelfrisch/Edifact/actions/workflows/psalm.yml/badge.svg)

Parse, build, serialize and validate UN/EDIFACT Messages.

You will likely have to generate your own Segments. See [php-edifact/edifact-mapping](https://github.com/php-edifact/edifact-mapping) for XML Mappings. I have done a [protype](https://github.com/Apfelfrisch/ediseg-generator) for autogeneration, it should give you a good starting point.

If you don't need validation or Segement getter you can also parse to the [Generic Segment](#parse-to-the-generic-segment).

## Highlights
* Parse and build UN/EDIFACT Messages in a memory efficient way
* Define your own Segments
* Validate the Message Segments

## Usage

### Parse EDIFACT Messages

#### Load Segements Classes

First you have to load your Segments withe the Factory. After that you mark the Factory as default.
```php
use Apfelfrisch\Edifact\Segment\SegmentFactory;

$segmentFactory = new SegmentFactory;
$segmentFactory->addSegment('SEQ', \My\Namespace\Segments\Seq::class);

SegmentFactory::setDefault($segmentFactory);

```
Or you inject the Builder in the Message Object:

```php
use Apfelfrisch\Edifact\Segment\SegmentFactory;

$message = Message::fromString("UNA:+.? 'SEQ+1", $segmentFactory);
```

If you don't need validation or Segment getter you can also parse to the Generic Sgement

```php
use Apfelfrisch\Edifact\Segments\Generic;
use Apfelfrisch\Edifact\Segment\SegmentFactory;

$segmentFactory = new SegmentFactory;
$segmentFactory->addFallback(Generic::class);
SegmentFactory::setDefault($segmentFactory);

```

#### Parse from String
```php
use Apfelfrisch\Edifact\Message;

$message = Message::fromString("UNA:+.? 'NAD+DP++++Musterstr.::10+City++12345+DE");
```

#### Parse from File
```php
use Apfelfrisch\Edifact\Message;

$message = Message::fromFilepath('path/to/file.txt');
```

#### Iterate over Segments
```php
use Apfelfrisch\Edifact\Segments\SegmentInterface;

foreach ($message->getSegments() as $segment) {
    if ($segment instanceof SegmentInterface) {
        echo $segment->name();
    }
}
```

#### Filter Segments
```php
foreach ($message->filterSegments(Nad::class) as $segment) {
    echo $segment->name(); // NAD
}

$message->filterSegments(Nad::class, fn(Nad $seg): bool 
    => $seg->street() === 'Musterstr.'
);

echo $message->findFirstSegment(Nad::class)->name(); // NAD
```

#### Unwrap Messages
```php
foreach ($message->unwrap() as $partialMessage) {
    echo $segment instanceof \Apfelfrisch\Edifact\Message;
}
```

#### Add Readfilter
```php
$message->addStreamFilter('iso-to-utf8', 'convert.iconv.ISO-8859-1.UTF-8');
```


### Build a Message:

#### Build with default Una

```php
use Apfelfrisch\Edifact\Builder;
use Apfelfrisch\Edifact\Message;
use My\Segment\MyUnb;
use My\Segment\MyUnh;

$builder = new Builder;

$builder->writeSegments(
    MyUnb::fromAttributes('1', '2', 'sender', '500', 'receiver', '400', new DateTime('2021-01-01 12:01:01'), 'unb-ref'),
    MyUnh::fromAttributes('unh-ref', 'type', 'v-no', 'r-no', 'o-no', 'o-co')
);

$message = new Message($builder->get());
```
UNA and the trailing Segments (UNT and UNZ) will be added automatically. If no UNA Segment is provided, it uses the default values [UNA:+.? ']. 
For now, the Space character and Decimal point will be ignored, you have to take care of it on Segment initialization.

#### Build with custom Una

```php
use Apfelfrisch\Edifact\Builder;
use Apfelfrisch\Edifact\Segemtn\UnaSegment;

$builder = new Builder(new UnaSegment('|', '#', '.', '!', ' ', '"'));
```

#### Write directly into File

```php
use Apfelfrisch\Edifact\Builder;
use Apfelfrisch\Edifact\Segemtn\UnaSegment;

$builder = new Builder(new UnaSegment, 'path/to/file.txt');
```

#### Add Writefilter to the Builder
```php
use Apfelfrisch\Edifact\Builder;

$builder = new Builder;
$builder->addStreamFilter('utf8-to-iso', 'convert.iconv.UTF-8.ISO-8859-1');
```

### Validate Message Segments
```php
use Apfelfrisch\Edifact\Message;
use Apfelfrisch\Edifact\Validation\Failure;
use Apfelfrisch\Edifact\Validation\Validator;

$message = Message::fromString("UNA:+.? 'SEQ+9999", $segmentFactory);

$validator = new Validator;

if(! $validator->isValid($message)) {
    foreach ($validator->getFailures() as $failure) {
        echo $failure instanceof Failure;
    }
}

```
