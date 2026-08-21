<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Validation;

/**
 * The reporting level of a syntax failure, matching the four levels
 * of a CONTRL message: UCI (interchange), UCM (message), UCS (segment)
 * and UCD (data element).
 */
enum ReportLevel
{
    case Interchange;
    case Message;
    case Segment;
    case DataElement;
}
