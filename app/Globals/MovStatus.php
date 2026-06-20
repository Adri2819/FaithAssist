<?php

namespace App\Globals;

final class MovStatus
{
    const PRE_ENROLLMENTS = 'preinscripciones';

    const ENROLLMENTS = 'inscripciones';

    const RE_ENROLLMENTS = 'reinscripciones';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return [
            self::PRE_ENROLLMENTS,
            self::ENROLLMENTS,
            self::RE_ENROLLMENTS,
        ];
    }
}
