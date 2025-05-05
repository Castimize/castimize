<?php

declare(strict_types=1);

namespace App\Enums\Finance;

enum BicEnum: string
{
    case RABONL2U = 'RABONL2U';
    case ABNANL2A = 'ABNANL2A';
    case INGBNL2A = 'INGBNL2A';
    case KNABNL2H = 'KNABNL2H';
    case SNSBNL2A = 'SNSBNL2A';
    case TRIONL2U = 'TRIONL2U';
    case RBRBNL21 = 'RBRBNL21';
    case ASNBNL21 = 'ASNBNL21';
    case BUNQNL2A = 'BUNQNL2A';
    case FVLBNL22 = 'FVLBNL22';

    public static function values(): array
    {
        $values = [];
        foreach (self::cases() as $case) {
            $values[$case->value] = $case->name;
        }

        return $values;
    }

    public static function getBicFromIban(string $iban): ?string
    {
        return match (true) {
            str_contains($iban, 'RABO') => self::RABONL2U->value,
            str_contains($iban, 'ABNA') => self::ABNANL2A->value,
            str_contains($iban, 'INGB') => self::INGBNL2A->value,
            str_contains($iban, 'KNAB') => self::KNABNL2H->value,
            str_contains($iban, 'SNSB') => self::SNSBNL2A->value,
            str_contains($iban, 'TRIO') => self::TRIONL2U->value,
            str_contains($iban, 'RBRB') => self::RBRBNL21->value,
            str_contains($iban, 'ASNB') => self::ASNBNL21->value,
            str_contains($iban, 'BUNQ') => self::BUNQNL2A->value,
            str_contains($iban, 'FVLB') => self::FVLBNL22->value,
        };
    }
}
