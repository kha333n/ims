<?php

use Carbon\Carbon;

if (! function_exists('formatDate')) {
    /**
     * Format a date as the IMS display format.
     * e.g. Carbon::parse('2025-04-16') → "16/Apr/2025"
     */
    function formatDate(Carbon|string|null $date): string
    {
        if ($date === null) {
            return '';
        }

        if (! $date instanceof Carbon) {
            $date = Carbon::parse($date);
        }

        return $date->format('d/M/Y');
    }
}
