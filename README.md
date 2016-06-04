# PHP - Edifact

![Build Status](https://travis-ci.org/Apfelfrisch/Edifact.svg?branch=master)

A PHP library, wich provides a Framework to parse, build, serialize and validate UN/EDIFACT messages.

Highlights
-------
* Parse and Write Files in a memory efficient and scalable way 
* Parse each Segment to its specific Class, this way we can define getter, setter and validation it
* Parse each Message to its specfic Class, see above.
* Validate the Message, with predefined rules. 
* Fully unit tested

Notes
-------
This Package only provides the basic Framework to work with Edifact Messages.
You have to define your needed Segment and Message Classes at your own. 
For a specific implementation see https://github.com/Apfelfrisch/EdiMessages. 
A Dummy implementation can be found in the tests.
