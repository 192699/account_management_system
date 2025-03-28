<?php
namespace App\Helpers;
class Helpers
{
    public static function generateAccountNumber()
    {
        do {
            $number = mt_rand(100000000000, 9999999999999999);
        } while (!self::isValidLuhn($number));

        return $number;
    }

    public static function isValidLuhn($number)
    {
        $digits = str_split(strrev($number));
        $sum = 0;

        foreach ($digits as $i => $digit) {
            $num = (int)$digit;
            if ($i % 2 !== 0) {
                $num *= 2;
                if ($num > 9) {
                    $num -= 9;
                }
            }
            $sum += $num;
        }

        return $sum % 10 === 0;
    }
}