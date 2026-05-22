<?php

namespace App\Support;

class PhoneHelper
{
    /**
     * Format a phone number as XXX-XXX-XXXX (North American style).
     */
    public static function format(?string $phoneNumber): string
    {
        if (empty($phoneNumber)) {
            return '';
        }

        $digits = preg_replace('/\D/', '', $phoneNumber);
        $digits = substr($digits, 0, 10);

        if (strlen($digits) === 0) {
            return '';
        }

        if (strlen($digits) <= 3) {
            return $digits;
        }

        if (strlen($digits) <= 6) {
            return substr($digits, 0, 3).'-'.substr($digits, 3);
        }

        return substr($digits, 0, 3).'-'.substr($digits, 3, 3).'-'.substr($digits, 6);
    }
}
