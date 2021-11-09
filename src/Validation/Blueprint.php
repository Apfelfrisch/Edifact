<?php

namespace Proengeno\Edifact\Validation;

use Proengeno\Edifact\Interfaces\SegInterface;
use Throwable;
use Proengeno\Edifact\Exceptions\ValidationException;

class Blueprint
{
    private int $loopDeep = 0;

    private array $blueprint = [];

    /** @param array<int, int> */
    private array $loopLevelCount = [];

    /** @param array<int, int> */
    private array $blueprintCount = [];

    private ?Throwable $optinalSegmentException = null;

    public function __construct(array $blueprint)
    {
        $this->flattenBlueprint($blueprint);
    }

    /**
     * @param SegInterface $segment
     *
     * @return void
     */
    public function validate($segment)
    {
        validationStart:
        if ($this->unnecessarySegmentIsMissing($segment) || $this->unnecessaryLoopIsMissing($segment)) {
            $this->countUpBlueprint();
            goto validationStart;
        }

        if ($this->startOfLoop()) {
            $this->nextLoop();
        } elseif ($this->startOfReLoop($segment)) {
            $this->reLoop();
        }

        if ($this->endOfLoop()) {
            $this->previosLoop();
            $this->validate($segment);
            return;
        }

        if ($segment->name() == $this->getExpectedSegmentName()) {
            try {
                $this->validateBlueprintTemplates($segment);
            } catch (ValidationException $e) {
                if ($this->getBlueprintAttribute('necessity') != 'O') {
                    throw $e;
                }
                $this->countUpBlueprint();
                $this->optinalSegmentException = $e;
                goto validationStart;
            }
            $this->countUpBlueprint();
            return;
        }

        if ($this->optinalSegmentException) {
            throw $this->optinalSegmentException;
        }

        throw ValidationException::unexpectedSegment(null, $segment->name(), $this->getExpectedSegmentName());
    }

    private function getExpectedSegmentName(): string
    {
        /** @psalm-suppress PossiblyInvalidCast */
        return (string)$this->getBlueprintAttribute('name');
    }

    private function validateBlueprintTemplates(SegInterface $segment): void
    {
        if (! is_array($templates = $this->getBlueprintAttribute('templates'))) {
            return;
        }

        foreach ($templates as $segmendMethod => $suggestions) {
            if (in_array($segment->$segmendMethod(), $suggestions)) {
                continue;
            }

            throw ValidationException::illegalContent(null, $segment->name(), $segment->$segmendMethod(), implode('" | "', $suggestions));
        }
    }

    private function flattenBlueprint(array $blueprint, int $nestedDeep = 0, int $levelCount = 0): void
    {
        $levelCounter = 0;
        foreach ($blueprint as $blueprintRow) {
            if (isset($blueprintRow['segments'])) {
                $this->flattenBlueprint($blueprintRow['segments'], $nestedDeep + 1, $levelCounter);
                $levelCounter++;
            }
            $this->blueprint[$nestedDeep][$levelCount][] = $blueprintRow;
        }
    }

    private function unnecessarySegmentIsMissing(SegInterface $segment): bool
    {
        return $this->getBlueprintAttribute('name') != 'LOOP'
            && $this->getBlueprintAttribute('necessity') == 'O'
            && $segment->name() != $this->getBlueprintAttribute('name');
    }

    private function unnecessaryLoopIsMissing(SegInterface $segment): bool
    {
        return $this->getBlueprintAttribute('name') == 'LOOP'
            && $segment->name() == $this->getBlueprintAttribute('name', $this->getBlueprintCount() + 1)
            && $this->getBlueprintAttribute('necessity') == 'O';
    }

    private function startOfLoop(): bool
    {
        return $this->getBlueprintAttribute('name') === 'LOOP';
    }

    private function reLoop(): void
    {
        $this->blueprintCount[$this->loopDeep] = 0;

        unset($this->loopLevelCount[$this->loopDeep + 1]);
    }

    private function endOfLoop(): bool
    {
        $segmentCount = isset($this->blueprint[$this->loopDeep][$this->getLevelLoopCount()])
            ? count($this->blueprint[$this->loopDeep][$this->getLevelLoopCount()])
            : 0;

        return $this->getBlueprintCount() >= $segmentCount;
    }

    private function previosLoop(): void
    {
        $this->loopDeep--;
        $this->countUpBlueprint();
    }

    private function startOfReLoop(SegInterface $segment): bool
    {
        if ($this->endOfLoop() && $segment->name() == $this->getBlueprintAttribute('name', 0)) {
            return true;
        }
        return false;
    }

    private function nextLoop(): void
    {
        $this->loopDeep++;
        $this->countUpLevelLoop();
        $this->blueprintCount[$this->loopDeep] = 0;
        unset($this->loopLevelCount[$this->loopDeep + 1]);
    }

    private function getLevelLoopCount(): int
    {
        if (!isset($this->loopLevelCount[$this->loopDeep])) {
            $this->loopLevelCount[$this->loopDeep] = 0;
        }

        /** @var int */
        return $this->loopLevelCount[$this->loopDeep];
    }

    private function countUpLevelLoop(): void
    {
        if (!isset($this->loopLevelCount[$this->loopDeep])) {
            $this->loopLevelCount[$this->loopDeep] = -1;
        }
        /** @psalm-suppress MixedOperand */
        $this->loopLevelCount[$this->loopDeep]++;
    }

    private function getBlueprintAttribute(string $attribute, ?int $blueprintCount = null): array|string|null
    {
        if ($blueprintCount === null) {
            $blueprintCount = $this->getBlueprintCount();
        }
        if (isset($this->blueprint[$this->loopDeep][$this->getLevelLoopCount()][$blueprintCount][$attribute])) {
            return $this->blueprint[$this->loopDeep][$this->getLevelLoopCount()][$blueprintCount][$attribute];
        }
        return null;
    }

    private function getBlueprintCount(): int
    {
        if (!isset($this->blueprintCount[$this->loopDeep])) {
            $this->blueprintCount[$this->loopDeep] = 0;
        }
        /** @var int */
        return $this->blueprintCount[$this->loopDeep];
    }

    private function countUpBlueprint(): void
    {
        if (!isset($this->blueprintCount[$this->loopDeep])) {
            $this->blueprintCount[$this->loopDeep] = 1;
        }
        /** @psalm-suppress MixedOperand */
        $this->blueprintCount[$this->loopDeep] ++;
    }
}
