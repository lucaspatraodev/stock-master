<?php

namespace App\Services;

class HtmlSanitizer
{
    public static function sanitize(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        $allowed = '<p><br><b><strong>';
        $clean = strip_tags($value, $allowed);
        $clean = preg_replace('/<(p|br|b|strong)\\s+[^>]*>/i', '<$1>', $clean);

        return $clean;
    }
}
