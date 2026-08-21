<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Test\Validation;

use Apfelfrisch\Edifact\Message;
use Apfelfrisch\Edifact\Segment\GenericSegment;
use Apfelfrisch\Edifact\StreamMessageFactory;
use Apfelfrisch\Edifact\Test\Fixtures\ValidationSegment;
use Apfelfrisch\Edifact\Test\TestCase;
use Apfelfrisch\Edifact\Validation\InterchangeValidator;
use Apfelfrisch\Edifact\Validation\ReportLevel;
use Apfelfrisch\Edifact\Validation\SyntaxErrorCode;

final class InterchangeValidatorTest extends TestCase
{
    public function test_a_valid_interchange_has_no_failures(): void
    {
        $message = $this->buildMessage(
            "UNB+UNOC:3+sender+receiver+210101:1201+REF1'"
            . "UNH+M1+ORDERS:D:96A:UN'SEQ+1'UNT+3+M1'"
            . "UNH+M2+ORDERS:D:96A:UN'SEQ+2'UNT+3+M2'"
            . "UNZ+2+REF1'",
        );

        static::assertSame([], new InterchangeValidator()->validate($message));
    }

    public function test_missing_unb_segment(): void
    {
        $failures = new InterchangeValidator()->validate($this->buildMessage(
            "UNH+M1+ORDERS:D:96A:UN'UNT+2+M1'UNZ+1+REF1'",
        ));

        static::assertCount(1, $failures);
        static::assertSame(ReportLevel::Interchange, $failures[0]->level);
        static::assertSame(SyntaxErrorCode::Missing, $failures[0]->errorCode);
        static::assertSame('UNB', $failures[0]->serviceSegment);
        static::assertSame(0, $failures[0]->messageCounter);
    }

    public function test_missing_unz_segment(): void
    {
        $failures = new InterchangeValidator()->validate($this->buildMessage(
            "UNB+UNOC:3+sender+receiver+210101:1201+REF1'UNH+M1+ORDERS:D:96A:UN'UNT+2+M1'",
        ));

        static::assertCount(1, $failures);
        static::assertSame(SyntaxErrorCode::Missing, $failures[0]->errorCode);
        static::assertSame('UNZ', $failures[0]->serviceSegment);
    }

    public function test_mismatching_interchange_references(): void
    {
        $failures = new InterchangeValidator()->validate($this->buildMessage(
            "UNB+UNOC:3+sender+receiver+210101:1201+REF1'UNH+M1+ORDERS:D:96A:UN'UNT+2+M1'UNZ+1+OTHER'",
        ));

        static::assertCount(1, $failures);
        static::assertSame(ReportLevel::Interchange, $failures[0]->level);
        static::assertSame(SyntaxErrorCode::ReferencesDoNotMatch, $failures[0]->errorCode);
        static::assertSame('UNZ', $failures[0]->serviceSegment);
        static::assertSame('OTHER', $failures[0]->value);
    }

    public function test_wrong_interchange_control_count(): void
    {
        $failures = new InterchangeValidator()->validate($this->buildMessage(
            "UNB+UNOC:3+sender+receiver+210101:1201+REF1'UNH+M1+ORDERS:D:96A:UN'UNT+2+M1'UNZ+5+REF1'",
        ));

        static::assertCount(1, $failures);
        static::assertSame(SyntaxErrorCode::ControlCountDoesNotMatch, $failures[0]->errorCode);
        static::assertSame('UNZ', $failures[0]->serviceSegment);
        static::assertSame('5', $failures[0]->value);
    }

    public function test_an_interchange_without_messages(): void
    {
        $failures = new InterchangeValidator()->validate($this->buildMessage(
            "UNB+UNOC:3+sender+receiver+210101:1201+REF1'UNZ+0+REF1'",
        ));

        static::assertCount(1, $failures);
        static::assertSame(SyntaxErrorCode::LowerLevelEmpty, $failures[0]->errorCode);
    }

    public function test_interchange_failures_suppress_message_level_findings(): void
    {
        // Both the UNZ count and the UNT reference are wrong; per the CONTRL
        // rules only the interchange level failure is reported.
        $failures = new InterchangeValidator()->validate($this->buildMessage(
            "UNB+UNOC:3+sender+receiver+210101:1201+REF1'UNH+M1+ORDERS:D:96A:UN'UNT+2+WRONG'UNZ+5+REF1'",
        ));

        static::assertCount(1, $failures);
        static::assertSame(ReportLevel::Interchange, $failures[0]->level);
        static::assertSame(SyntaxErrorCode::ControlCountDoesNotMatch, $failures[0]->errorCode);
    }

    public function test_mismatching_message_references(): void
    {
        $failures = new InterchangeValidator()->validate($this->buildMessage(
            "UNB+UNOC:3+sender+receiver+210101:1201+REF1'UNH+M1+ORDERS:D:96A:UN'UNT+2+WRONG'UNZ+1+REF1'",
        ));

        static::assertCount(1, $failures);
        static::assertSame(ReportLevel::Message, $failures[0]->level);
        static::assertSame(SyntaxErrorCode::ReferencesDoNotMatch, $failures[0]->errorCode);
        static::assertSame('UNT', $failures[0]->serviceSegment);
        static::assertSame(1, $failures[0]->messageCounter);
        static::assertSame('M1', $failures[0]->messageReference);
        static::assertSame('WRONG', $failures[0]->value);
    }

    public function test_wrong_message_segment_count(): void
    {
        $failures = new InterchangeValidator()->validate($this->buildMessage(
            "UNB+UNOC:3+sender+receiver+210101:1201+REF1'UNH+M1+ORDERS:D:96A:UN'SEQ+1'UNT+9+M1'UNZ+1+REF1'",
        ));

        static::assertCount(1, $failures);
        static::assertSame(SyntaxErrorCode::ControlCountDoesNotMatch, $failures[0]->errorCode);
        static::assertSame('UNT', $failures[0]->serviceSegment);
        static::assertSame('9', $failures[0]->value);
    }

    public function test_message_without_unt_segment(): void
    {
        $failures = new InterchangeValidator()->validate($this->buildMessage(
            "UNB+UNOC:3+sender+receiver+210101:1201+REF1'UNH+M1+ORDERS:D:96A:UN'SEQ+1'"
            . "UNH+M2+ORDERS:D:96A:UN'UNT+2+M2'UNZ+2+REF1'",
        ));

        static::assertCount(1, $failures);
        static::assertSame(SyntaxErrorCode::Missing, $failures[0]->errorCode);
        static::assertSame('UNT', $failures[0]->serviceSegment);
        static::assertSame(1, $failures[0]->messageCounter);
    }

    public function test_duplicate_message_references(): void
    {
        $failures = new InterchangeValidator()->validate($this->buildMessage(
            "UNB+UNOC:3+sender+receiver+210101:1201+REF1'"
            . "UNH+M1+ORDERS:D:96A:UN'UNT+2+M1'"
            . "UNH+M1+ORDERS:D:96A:UN'UNT+2+M1'"
            . "UNZ+2+REF1'",
        ));

        static::assertCount(1, $failures);
        static::assertSame(SyntaxErrorCode::DuplicateDetected, $failures[0]->errorCode);
        static::assertSame(2, $failures[0]->messageCounter);
        static::assertSame('M1', $failures[0]->messageReference);
    }

    public function test_segments_outside_of_a_message(): void
    {
        $failures = new InterchangeValidator()->validate($this->buildMessage(
            "UNB+UNOC:3+sender+receiver+210101:1201+REF1'SEQ+1'UNH+M1+ORDERS:D:96A:UN'UNT+2+M1'UNZ+1+REF1'",
        ));

        static::assertCount(1, $failures);
        static::assertSame(ReportLevel::Segment, $failures[0]->level);
        static::assertSame(SyntaxErrorCode::NotSupportedAtPosition, $failures[0]->errorCode);
        static::assertSame('SEQ', $failures[0]->value);
    }

    public function test_segments_after_the_interchange_trailer(): void
    {
        $failures = new InterchangeValidator()->validate($this->buildMessage(
            "UNB+UNOC:3+sender+receiver+210101:1201+REF1'UNH+M1+ORDERS:D:96A:UN'UNT+2+M1'UNZ+1+REF1'SEQ+1'",
        ));

        static::assertCount(1, $failures);
        static::assertSame(SyntaxErrorCode::TooManyConstituents, $failures[0]->errorCode);
        static::assertSame('SEQ', $failures[0]->value);
    }

    public function test_multiple_segments_after_the_interchange_trailer_are_reported_once(): void
    {
        $failures = new InterchangeValidator()->validate($this->buildMessage(
            "UNB+UNOC:3+sender+receiver+210101:1201+REF1'UNH+M1+ORDERS:D:96A:UN'UNT+2+M1'UNZ+1+REF1'SEQ+1'SEQ+2'",
        ));

        static::assertCount(1, $failures);
        static::assertSame(SyntaxErrorCode::TooManyConstituents, $failures[0]->errorCode);
        static::assertSame('SEQ', $failures[0]->value);
    }

    public function test_a_duplicate_unb_segment(): void
    {
        $failures = new InterchangeValidator()->validate($this->buildMessage(
            "UNB+UNOC:3+sender+receiver+210101:1201+REF1'"
            . "UNB+UNOC:3+sender+receiver+210101:1201+REF1'"
            . "UNH+M1+ORDERS:D:96A:UN'UNT+2+M1'UNZ+1+REF1'",
        ));

        static::assertCount(1, $failures);
        static::assertSame(SyntaxErrorCode::NotSupportedAtPosition, $failures[0]->errorCode);
        static::assertSame('UNB', $failures[0]->serviceSegment);
    }

    public function test_a_missing_message_reference_skips_the_trailer_reference_check(): void
    {
        $failures = new InterchangeValidator()->validate($this->buildMessage(
            "UNB+UNOC:3+sender+receiver+210101:1201+REF1'UNH++ORDERS:D:96A:UN'UNT+2+X'UNZ+1+REF1'",
        ));

        static::assertCount(1, $failures);
        static::assertSame(SyntaxErrorCode::Missing, $failures[0]->errorCode);
        static::assertSame('UNH', $failures[0]->serviceSegment);
    }

    public function test_every_failing_message_reports_all_its_failures(): void
    {
        $failures = new InterchangeValidator()->validate($this->buildMessage(
            "UNB+UNOC:3+sender+receiver+210101:1201+REF1'"
            . "UNH+M1+ORDERS:D:96A:UN'UNT+9+WRONG'"
            . "UNH+M2+ORDERS:D:96A:UN'UNT+2+ALSO'"
            . "UNZ+2+REF1'",
        ));

        static::assertCount(3, $failures);

        // Message one reports its wrong control count and its wrong reference
        static::assertSame(SyntaxErrorCode::ControlCountDoesNotMatch, $failures[0]->errorCode);
        static::assertSame(1, $failures[0]->messageCounter);
        static::assertSame(SyntaxErrorCode::ReferencesDoNotMatch, $failures[1]->errorCode);
        static::assertSame(1, $failures[1]->messageCounter);

        // Message two reports its wrong reference
        static::assertSame(SyntaxErrorCode::ReferencesDoNotMatch, $failures[2]->errorCode);
        static::assertSame(2, $failures[2]->messageCounter);
    }

    public function test_validating_segments_against_their_blueprints(): void
    {
        ValidationSegment::$ruleOne = 'M|n|2';
        ValidationSegment::$ruleTwo = null;

        $failures = new InterchangeValidator()->validate($this->buildMessage("UNB+UNOC:3+sender+receiver+210101:1201+REF1'UNH+M1+ORDERS:D:96A:UN'SEG+AB'UNT+3+M1'UNZ+1+REF1'", [
            'SEG' => ValidationSegment::class,
        ]));

        static::assertCount(1, $failures);
        static::assertSame(ReportLevel::DataElement, $failures[0]->level);
        static::assertSame(SyntaxErrorCode::InvalidCharacterType, $failures[0]->errorCode);
        static::assertSame(1, $failures[0]->messageCounter);
        static::assertSame('M1', $failures[0]->messageReference);
        static::assertSame(2, $failures[0]->segmentPosition);
        static::assertSame(1, $failures[0]->elementPosition);
        static::assertSame(0, $failures[0]->componentPosition);
        static::assertSame('AB', $failures[0]->value);
    }

    public function test_message_failures_suppress_its_segment_findings_but_not_other_messages(): void
    {
        ValidationSegment::$ruleOne = 'M|n|2';
        ValidationSegment::$ruleTwo = null;

        $failures = new InterchangeValidator()->validate($this->buildMessage("UNB+UNOC:3+sender+receiver+210101:1201+REF1'"
        . "UNH+M1+ORDERS:D:96A:UN'SEG+AB'UNT+3+WRONG'"
        . "UNH+M2+ORDERS:D:96A:UN'SEG+CD'UNT+3+M2'"
        . "UNZ+2+REF1'", ['SEG' => ValidationSegment::class]));

        static::assertCount(2, $failures);

        // Message one only reports its UNT failure, the blueprint findings are dropped
        static::assertSame(SyntaxErrorCode::ReferencesDoNotMatch, $failures[0]->errorCode);
        static::assertSame(1, $failures[0]->messageCounter);

        // Message two is still fully checked
        static::assertSame(SyntaxErrorCode::InvalidCharacterType, $failures[1]->errorCode);
        static::assertSame(2, $failures[1]->messageCounter);
        static::assertSame('CD', $failures[1]->value);
    }

    /**
     * @param array<string, class-string<\Apfelfrisch\Edifact\Segment\Segment>> $segments
     */
    private function buildMessage(string $ediContent, array $segments = []): Message
    {
        $factory = new StreamMessageFactory()->addFallback(GenericSegment::class);

        foreach ($segments as $name => $segmentClass) {
            $factory->addSegment($name, $segmentClass);
        }

        return $factory->fromString($ediContent);
    }
}
