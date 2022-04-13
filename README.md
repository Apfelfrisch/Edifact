# PHP - EDIFACT

![Unit test](https://github.com/Apfelfrisch/Edifact/actions/workflows/phpunit.yml/badge.svg)
![Static Analysis](https://github.com/Apfelfrisch/Edifact/actions/workflows/psalm.yml/badge.svg)
![Mutation tests](https://github.com/Apfelfrisch/Edifact/actions/workflows/infection.yml/badge.svg)

Parse, build, serialize and validate UN/EDIFACT Messages in a memory efficient way.

You will likely have to generate your own Segments, see [php-edifact/edifact-mapping](https://github.com/php-edifact/edifact-mapping) for XML Mappings. I have done a [protype](https://github.com/Apfelfrisch/ediseg-generator) for autogeneration, it should give you a good starting point.

If you don't need validation or Segment getter you can also parse to the generic Segment.

## Usage

### Parse EDIFACT Messages

#### Load Segment Classes

You can add your Segments to the Factory like so:
```php
use Apfelfrisch\Edifact\Segment\SegmentFactory;

$segmentFactory = new SegmentFactory;
$segmentFactory->addSegment('SEQ', \My\Namespace\Segments\Seq::class);

```
After that you can either mark the Factory as default:
```php
$segmentFactory->markAsDefault();
```
or you inject the Factory in the Message:
```php
use Apfelfrisch\Edifact\Segment\SegmentFactory;

$message = Message::fromString("UNA:+.? 'SEQ+1", $segmentFactory);
```

If you don't need validation or Segment getter you can also parse to the generic Segement

```php
use Apfelfrisch\Edifact\Segments\Generic;
use Apfelfrisch\Edifact\Segment\SegmentFactory;

$segmentFactory = new SegmentFactory;
$segmentFactory->addFallback(Generic::class);
$segmentFactory->markAsDefault();

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
use My\Namespace\Segments\MyNad;

foreach ($message->filterSegments(MyNad::class) as $segment) {
    echo $segment->name(); // NAD
}

$message->filterSegments(MyNad::class, fn(Nad $seg): bool 
    => $seg->street() === 'Musterstr.'
);

echo $message->findFirstSegment(MyNad::class)->name(); // NAD
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
use My\Namespace\Segments\MyUnb;
use My\Namespace\Segments\MyUnh;

$builder = new Builder;

$builder->writeSegments(
    MyUnb::fromAttributes('1', '2', 'sender', '500', 'receiver', '400', new DateTime('2021-01-01 12:01:01'), 'unb-ref'),
    MyUnh::fromAttributes('unh-ref', 'type', 'v-no', 'r-no', 'o-no', 'o-co')
);

$message = new Message($builder->get());
```
UNA and the trailing Segments (UNT and UNZ) will be added automatically. If no UNA Segment is provided, it uses the default values [UNA:+.? ']. 

#### Build with custom Una

```php
use Apfelfrisch\Edifact\Builder;
use Apfelfrisch\Edifact\Segment\UnaSegment;

$builder = new Builder(new UnaSegment('|', '#', ',', '!', '_', '"'));
```
If you replace the decimal seperator, be sure that the blueprint marks the value as numeric.

#### Write directly into File

```php
use Apfelfrisch\Edifact\Builder;
use Apfelfrisch\Edifact\Segment\UnaSegment;

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
