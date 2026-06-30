<?php

namespace App\Globals;

final class Sex
{
    const MALE = 'male';

    const FEMALE = 'female';

    public static function values(): array
    {
        return [
            self::MALE,
            self::FEMALE,
        ];
    }
}
