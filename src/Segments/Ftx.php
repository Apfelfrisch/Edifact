<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\Elements;

class Ftx extends AbstractSegment
{
    private static ?Elements $blueprint = null;

    public static function blueprint(): Elements
    {
        if (self::$blueprint === null) {
            self::$blueprint = (new Elements)
                ->addValue('FTX', 'FTX', 'M|a|3')
                ->addValue('4451', '4451', 'M|an|..3')
                ->addValue('4453', '4453', null)
                ->addValue('C107', '4441', null)
                ->addValue('C108', '4440:1', 'O|an|..512')
                ->addValue('C108', '4440:2', 'O|an|..512')
                ->addValue('C108', '4440:3', 'O|an|..512')
                ->addValue('C108', '4440:4', 'O|an|..512')
                ->addValue('C108', '4440:5', 'O|an|..512');
        }

        return self::$blueprint;
    }

    public static function fromAttributes(string $qualifier, ?string $message = null, ?string $code = null): self
    {
        return new self((new Elements)
            ->addValue('FTX', 'FTX', 'FTX')
            ->addValue('4451', '4451', $qualifier)
            ->addValue('4453', '', null)
            ->addValue('C107', '4441', $code)
            ->addValue('C108', '4440:1', $message !== null ? substr($message, 0, 512) : null)
            ->addValue('C108', '4440:2', $message !== null ? substr($message, 512, 512) : null)
            ->addValue('C108', '4440:3', $message !== null ? substr($message, 1024, 512) : null)
            ->addValue('C108', '4440:4', $message !== null ? substr($message, 1536, 512) : null)
            ->addValue('C108', '4440:5', $message !== null ? substr($message, 2048, 512) : null)
        );
    }

    public function qualifier(): ?string
    {
        return $this->elements->getValue('4451', '4451');
    }

    public function code(): ?string
    {
        return $this->elements->getValue('C107', '4441');
    }

    public function message(): ?string
    {
        $message = null;
        $i = 1;
        while (null !== $value = $this->elements->getValue('C108', "4440:$i")) {
            $message = $message === null ? $value : $message . $value;
            $i++;
        }
        return $message;
    }
}
