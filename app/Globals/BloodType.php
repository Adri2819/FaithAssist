<?php

namespace App\Globals;

final class BloodType
{
    const A_POSITIVE = 'a_positive';

    const A_NEGATIVE = 'a_negative';

    const B_POSITIVE = 'b_positive';

    const B_NEGATIVE = 'b_negative';

    const AB_POSITIVE = 'ab_positive';

    const AB_NEGATIVE = 'ab_negative';

    const O_POSITIVE = 'o_positive';

    const O_NEGATIVE = 'o_negative';

    const UNKNOWN = 'unknown';

    public static function values(): array
    {
        return [
            self::A_POSITIVE,
            self::A_NEGATIVE,
            self::B_POSITIVE,
            self::B_NEGATIVE,
            self::AB_POSITIVE,
            self::AB_NEGATIVE,
            self::O_POSITIVE,
            self::O_NEGATIVE,
            self::UNKNOWN,
        ];
    }
}
