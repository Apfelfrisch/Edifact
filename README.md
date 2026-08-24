# PHP - EDIFACT

![Unit test](https://github.com/Apfelfrisch/Edifact/actions/workflows/phpunit.yml/badge.svg)
![Static Analysis](https://github.com/Apfelfrisch/Edifact/actions/workflows/mago.yml/badge.svg)
![Mutation tests](https://github.com/Apfelfrisch/Edifact/actions/workflows/infection.yml/badge.svg)

Parse, build, serialize and validate UN/EDIFACT messages in a memory efficient way.

## Installation

```bash
composer require apfelfrisch/edifact
```

Version 2.0 requires PHP 8.4+. If you are on PHP 8.1–8.3, use the [1.x releases](https://github.com/Apfelfrisch/Edifact/tree/master) (`composer require apfelfrisch/edifact:^1.3`).

See the [CHANGELOG](CHANGELOG.md) for what is new in 2.0, including the breaking changes when upgrading from 1.x.

You will likely have to generate your own Segments, see [php-edifact/edifact-mapping](https://github.com/php-edifact/edifact-mapping) for XML mappings. I have done a [prototype](https://github.com/Apfelfrisch/ediseg-generator) for autogeneration, it should give you a good starting point.

If you don't need validation or segment getters you can also parse to the `GenericSegment`.

## Usage

### Parse EDIFACT Messages

#### Load Segment Classes

You can add your Segments to the factory like so:

```php
use Apfelfrisch\Edifact\StreamMessageFactory;

$factory = new StreamMessageFactory;
$factory->addSegment('SEQ', \My\Namespace\Segments\Seq::class);
```

After that you can either mark the factory as default:

```php
$factory->markAsDefault();
```

or use the factory directly:

```php
$message = $factory->fromString("UNA:+.? 'SEQ+1");
```

If you don't need validation or segment getters you can also parse to the `GenericSegment`:

```php
use Apfelfrisch\Edifact\Segment\GenericSegment;
use Apfelfrisch\Edifact\StreamMessageFactory;

$factory = new StreamMessageFactory;
$factory->addFallback(GenericSegment::class);
$factory->markAsDefault();
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
foreach ($message->getSegments() as $segment) {
    echo $segment->name();
}
```

#### Filter Segments

```php
use My\Namespace\Segments\MyNad;

foreach ($message->filterSegments(MyNad::class) as $segment) {
    echo $segment->name(); // NAD
}

$message->filterAllSegments(MyNad::class, fn(MyNad $seg): bool
    => $seg->street() === 'Musterstr.'
);

echo $message->findFirstSegment(MyNad::class)?->name(); // NAD
```

#### Unwrap Messages

Splits an interchange into one message per `UNH`...`UNT` block (the segment names are configurable).

```php
foreach ($message->unwrap() as $partialMessage) {
    echo $partialMessage instanceof \Apfelfrisch\Edifact\Message;
}

foreach ($message->unwrap('HDR', 'TRL') as $partialMessage) {
    // custom header and trailer
}
```

The partial messages above live in memory. If you need every partial as its own `Stream` (e.g. to keep the
raw segment lines, the file handle or to build your own wrapper around it), unwrap the stream instead and hand
each partial to the factory. A leading `UNA` segment is copied into every partial, escaped segment terminators
stay escaped.

```php
use Apfelfrisch\Edifact\Iterators\Stream\Stream;
use Apfelfrisch\Edifact\StreamMessageFactory;

$factory = new StreamMessageFactory;
$stream = new Stream('path/to/interchange.txt');

foreach ($stream->unwrap() as $partialStream) {
    $partialMessage = $factory->fromStream($partialStream);
}
```

`fromStream()` uses the given stream as is; the factory's read filters are only applied to streams it opens
itself via `fromString()` / `fromFilepath()`.

#### Add Readfilter

```php
use Apfelfrisch\Edifact\StreamMessageFactory;

$factory = new StreamMessageFactory;
$factory->addStreamFilter('convert.iconv.ISO-8859-1.UTF-8');
```

### Build a Message

#### Build with default Una

```php
use Apfelfrisch\Edifact\Builder;
use My\Namespace\Segments\MyUnb;
use My\Namespace\Segments\MyUnh;

$builder = new Builder;

$builder->writeSegments(
    MyUnb::fromAttributes('1', '2', 'sender', '500', 'receiver', '400', new DateTime('2021-01-01 12:01:01'), 'unb-ref'),
    MyUnh::fromAttributes('unh-ref', 'type', 'v-no', 'r-no', 'o-no', 'o-co'),
);

$stream = $builder->get();
```

UNA and the trailing Segments (UNT and UNZ) will be added automatically. If no UNA Segment is provided, it uses the default values [UNA:+.? '].

#### Build with custom Una

```php
use Apfelfrisch\Edifact\Builder;
use Apfelfrisch\Edifact\Segment\UnaSegment;

$builder = new Builder(new UnaSegment('|', '#', ',', '!', '_', '"'));
```

If you replace the decimal separator, be sure that the blueprint marks the value as numeric.

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
$builder->addStreamFilter('convert.iconv.UTF-8.ISO-8859-1');
```

### Validate a complete Interchange (CONTRL-style syntax check)

The `InterchangeValidator` checks the structure of a whole transmission file
(UNB/UNZ and UNH/UNT service segments, matching references, control counts,
duplicate message references) plus the blueprints of all registered segments,
following the CONTRL check order: an interchange level failure stops the
check, a failing message reports only its UNH/UNT failures while other
messages are still checked.

Every `SyntaxFailure` carries the UN/EDIFACT error code (service code
list 0085) and the context needed to fill the UCI/UCM/UCS/UCD segments of a
CONTRL message: the affected service segment, the message counter and
reference, the segment position within the message and the element/component
positions.

```php
use Apfelfrisch\Edifact\Message;
use Apfelfrisch\Edifact\Validation\InterchangeValidator;

$message = Message::fromFilepath('path/to/interchange.txt');

foreach (new InterchangeValidator()->validate($message) as $failure) {
    echo $failure->errorCode->value;    // e.g. 28
    echo $failure->description();       // "References do not match"
    echo $failure->serviceSegment;      // e.g. "UNT"
    echo $failure->messageCounter;      // n-th message of the interchange
    echo $failure->segmentPosition;     // position within the message, UNH = 1
}
```

Checks that need knowledge the file itself cannot provide (own MP-ID,
known senders, already received interchange references, message type
descriptions) remain the concern of the application; `SyntaxErrorCode`
provides the matching codes (7, 23, 25, 26, 35, 36, …) for reporting them.

### Validate Message Segments

```php
use Apfelfrisch\Edifact\Message;
use Apfelfrisch\Edifact\Validation\Validator;

$message = Message::fromString("UNA:+.? 'SEQ+9999");

$validator = new Validator;

if (! $validator->isValid($message)) {
    foreach ($validator->getFailures() as $failure) {
        echo $failure->message;
    }
}
```

## Development

The project uses [Mago](https://mago.carthage.software) for formatting, linting and static analysis, and [PHPUnit](https://phpunit.de) for tests:

```bash
composer fmt      # format the code
composer lint     # lint the code
composer analyze  # run static analysis
composer test     # run the test suite
composer check    # run everything
```
