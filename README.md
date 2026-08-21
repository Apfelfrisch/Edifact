# PHP - EDIFACT

![Unit test](https://github.com/Apfelfrisch/Edifact/actions/workflows/phpunit.yml/badge.svg)
![Static Analysis](https://github.com/Apfelfrisch/Edifact/actions/workflows/mago.yml/badge.svg)
![Mutation tests](https://github.com/Apfelfrisch/Edifact/actions/workflows/infection.yml/badge.svg)

Parse, build, serialize and validate UN/EDIFACT messages in a memory efficient way.

Requires PHP 8.5+.

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

```php
foreach ($message->unwrap() as $partialMessage) {
    echo $partialMessage instanceof \Apfelfrisch\Edifact\Message;
}
```

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
