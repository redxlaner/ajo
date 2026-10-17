<?php

namespace App\Helpers;

class TextHelper
{
    public static function linkify(string $text): string
    {
        $escaped = e($text);

        return preg_replace(
            '~(https?://[^\s<]+)~i',
            '<a href="$1" target="_blank" rel="noopener" class="text-indigo-600 hover:underline break-all">$1</a>',
            nl2br($escaped)
        );
    }
}