<?php

namespace App\Helpers;

class LuhnHelper
{
    public static function generateAccountNumber(): int
    {
        do {
            // Generate a random number between 12-16 digits
            $length = rand(12, 16);
            $number = mt_rand(1, 9); // First digit can't be 0
            
            // Generate remaining digits
            for ($i = 1; $i < $length - 1; $i++) {
                $number .= mt_rand(0, 9);
            }
            
            // Calculate check digit
            $checkDigit = self::calculateCheckDigit($number);
            $number .= $checkDigit;
            
        } while (!self::validate($number)); // Ensure the number is valid
        
        return (int) $number;
    }

    public static function validate(string $number): bool
    {
        $sum = 0;
        $length = strlen($number);
        $parity = $length % 2;

        for ($i = $length - 1; $i >= 0; $i--) {
            $digit = (int) $number[$i];
            
            if ($i % 2 == $parity) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            
            $sum += $digit;
        }

        return $sum % 10 === 0;
    }

    private static function calculateCheckDigit(string $number): int
    {
        $sum = 0;
        $length = strlen($number);
        $parity = $length % 2;

        for ($i = $length - 1; $i >= 0; $i--) {
            $digit = (int) $number[$i];
            
            if ($i % 2 == $parity) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            
            $sum += $digit;
        }

        return (10 - ($sum % 10)) % 10;
    }
} 