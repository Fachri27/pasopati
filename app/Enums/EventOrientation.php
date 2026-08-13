<?php

namespace App\Enums;

enum EventOrientation: string
{
    case Landscape = 'landscape';
    case Horizontal = 'horizontal';

    public function label(): string
    {
        return match ($this) {
            self::Landscape => 'Landscape',
            self::Horizontal => 'Horizontal',
        };
    }

    public function aspectRatio(): string
    {
        return match ($this) {
            self::Landscape => '16 / 9',
            self::Horizontal => '4 / 3',
        };
    }
}
