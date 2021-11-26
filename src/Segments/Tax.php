<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\DataGroups;

class Tax extends AbstractSegment
{
    private static ?DataGroups $blueprint = null;

    public static function blueprint(): DataGroups
    {
        if (self::$blueprint === null) {
            self::$blueprint = (new DataGroups)
                ->addValue('TAX' , 'TAX' , 'M|a|3')
                ->addValue('5283', '5283', 'M|n|3')
                ->addValue('C241', '5153', 'M|n|3')
                ->addValue('C533', '5289', null)
                ->addValue('5286', '5286', null)
                ->addValue('C243', '5279', null)
                ->addValue('C243', '1131', null)
                ->addValue('C243', '3055', null)
                ->addValue('C243', '5278', 'D|n|17')
                ->addValue('5305', '5305', 'M|an|3');
        }

        return self::$blueprint;
    }

    public static function fromAttributes(string $qualifier, string $type, string $rate, string $category): self
    {
        return new self((new DataGroups)
            ->addValue('TAX', 'TAX', 'TAX')
            ->addValue('5283', '5283', $qualifier)
            ->addValue('C241', '5153', $type)
            ->addValue('C533', '5289', null)
            ->addValue('5286', '5286', null)
            ->addValue('C243', '5279', null)
            ->addValue('C243', '1131', null)
            ->addValue('C243', '3055', null)
            ->addValue('C243', '5278', $rate)
            ->addValue('5305', '5305', $category)
        );
    }

    public function qualifier(): ?string
    {
        return $this->elements->getValue('5283', '5283');
    }

    public function type(): ?string
    {
        return $this->elements->getValue('C241', '5153');
    }

    public function rate(): ?string
    {
        return $this->elements->getValue('C243', '5278');
    }

    public function category(): ?string
    {
        return $this->elements->getValue('5305', '5305');
    }
}
