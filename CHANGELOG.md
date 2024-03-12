# Changelog

1.2.1
 - fix Terminate segment terminator

1.1.1
 - Remove obsolete Respect\Validation from dependencies

1.1.0
 - Specify PHP version
 - Remove Respect\Validation from Code
 - Fix MessageCount on Validator
 - Fix Bug on filepath when Message is in memory
 - Mark SeglineParser::getUnaSegment as deprecated
 - Throw Exception when adding a Segment to finalize build
 - Fix message unwrapping when escape charcter is needed

1.0
 - Remove obsolete Apfelfrisch\Edifact\Stream::setUnaSegment
 - Remove obsolete Apfelfrisch\Edifact\Segment\AbstractSegment::toString
 - Remove obsolete Apfelfrisch\Edifact\Segment\GenericSegment::toString
 - Remove Apfelfrisch\Edifact\Stream\Stream::getFirst
 - Remove Apfelfrisch\Edifact\Stream\Stream::getCurrent
 - Add Apfelfrisch\Edifact\Exceptions\InvalidEdifactContentException
 - Add Apfelfrisch\Edifact\Exceptions\InvalidStreamException
 - Add Apfelfrisch\Edifact\Exceptions\ValidationException

0.11.0

- Deprecate Apfelfrisch\Edifact\Stream\Stream::getFirst
- Deprecate Apfelfrisch\Edifact\Stream\Stream::getCurrent
- Disable GenericSegment parsing as default
- Add Full Una Support
