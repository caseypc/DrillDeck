<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;

final class PhoneNumber
{
    public static function normalize(?string $phone): ?string
    {
        $phone=trim((string)$phone);
        if ($phone==='') return null;
        $international=str_starts_with($phone,'+');
        $digits=preg_replace('/\D+/', '',$phone);
        if (!$international && strlen($digits)===10) $digits='1'.$digits;
        if (!$international && strlen($digits)===11 && str_starts_with($digits,'1')) {
            // Already has implicit North American dialing code.
        } elseif (!$international) {
            throw ValidationException::withMessages(['phone'=>'Enter a 10-digit US/Canada number or international number starting with +.']);
        }
        if (!preg_match('/^[1-9]\d{7,14}$/',$digits)) {
            throw ValidationException::withMessages(['phone'=>'Enter a valid E.164 phone number.']);
        }
        return '+'.$digits;
    }

    public static function display(?string $phone): string
    {
        $digits=preg_replace('/\D+/','',(string)$phone);
        if (strlen($digits)===11 && str_starts_with($digits,'1')) {
            return sprintf('(%s) %s-%s',substr($digits,1,3),substr($digits,4,3),substr($digits,7,4));
        }
        return $phone ?? '';
    }
}
