# Changelog

2.0.0 (unreleased)
 - Add InterchangeValidator for CONTRL-style syntax checks of complete
   transmission files (UNB/UNZ and UNH/UNT structure, references, control
   counts, duplicates) with SyntaxFailure/SyntaxErrorCode/ReportLevel
   carrying UN/EDIFACT 0085 codes and UCI/UCM/UCS/UCD positions
 - Require PHP 8.5
 - Replace Phpstan, Psalm and PHP-CS-Fixer with Mago (formatter, linter and static analyzer)
 - Update PHPUnit to 12, Infection to 0.31
 - Make all classes final; Failure is now a readonly value object with public properties
   (the getter methods and the mutable setMessageCounter/setUnhCounter are gone,
   use the properties and Failure::withCounters instead)
 - Fix misspelled Failure type constants: UNKNOWN_ELEMENT / UNKNOWN_COMPONENT
   (values changed to 'unknownElement' / 'unknownComponent') and validation messages
 - Remove the undeclared webmozart/assert dependency from UnaSegment
 - Implement the previously empty InMemoryIterator to build messages from segments in memory
 - Builder, GenericSegment: constructors are private/promoted, behavior unchanged
 - Moa test fixture: fromAttributes no longer takes a decimal separator
 - Rff/Unh test fixtures: referenz() renamed to reference()
 - Stream: toString() no longer relies on error suppression; segment lines consisting of '0' parse correctly

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
