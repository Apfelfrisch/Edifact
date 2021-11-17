# PHP - Edifact

![Unit Test](https://github.com/Apfelfrisch/Edifact/actions/workflows/phpunit.yml/badge.svg)
![Static Analysis](https://github.com/Apfelfrisch/Edifact/actions/workflows/psalm.yml/badge.svg)

A PHP library, wich provides a Framework to parse, build, serialize and validate UN/EDIFACT messages.

Highlights
-------
* Parse and Write Files in a memory efficient and scalable way 
* Parse each Segment to its specific Object, this way we can define getter, setter and validation it
* Parse each Message to its specfic Object, see above.
* Validate the Message, with predefined rules. 

Usage
-------
```php
use Proengeno\Edifact\Edifact;
use \Proengeno\Edifact\Message\Segments\Nad;

$edifact = new Edifact;

$message = $edifact->resolveFromString("UNA:+.? 'NAD+DP++++Musterstr.::10+City++12345+DE");

foreach ($message as $segment) {
    if ($segment instanceof Nad) {
        echo $segment->street(); // Musterstr.
    }
}
```

Notes
-------
This Package only provides the basic Framework to work with Edifact Messages.
You have to define your needed Segment and Message Classes at your own. 
A Dummy implementation can be found in the tests.
