# PHP - Edifact

![Build Status](https://travis-ci.org/Apfelfrisch/EDI-Energy.svg?branch=master)
[![Quality Score](https://scrutinizer-ci.com/g/Apfelfrisch/Edifact/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Apfelfrisch/Edifact/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/Apfelfrisch/Edifact/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Apfelfrisch/Edifact/?branch=master)

A PHP library, wich provides a Framework to parse, build, serialize and validate UN/EDIFACT messages.

Highlights
-------
* Parse and Write Files in a memory efficient and scalable way 
* Parse each Segment to its specific Object, this way we can define getter, setter and validation it
* Parse each Message to its specfic Object, see above.
* Validate the Message, with predefined rules. 

Notes
-------
This Package only provides the basic Framework to work with Edifact Messages.
You have to define your needed Segment and Message Classes at your own. 
A Dummy implementation can be found in the tests.
