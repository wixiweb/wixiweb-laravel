<?php

if (!function_exists('trans_plural')) {
    function trans_plural(string $singular, string $plural, int $count, array $replace = [], $locale = null) : string {
        $transChoiceString = "[0,1] $singular|[2,*] $plural";
        return trans_choice($transChoiceString, $count, $replace, $locale);
    }
}

if (!function_exists('trans_plural_map')) {
    function trans_plural_map(array $strings, int $count, array $replace = [], $locale = null) : string {
        $transChoiceStrings = [];
        foreach ($strings as $choice => $string) {
            $transChoiceStrings[] = "[$choice] $string";
        }

        $transChoiceString = implode('|', $transChoiceStrings);
        return trans_choice($transChoiceString, $count, $replace, $locale);
    }
}
