<?php

if (! function_exists('formatMoney')) {
    /**
     * Format a paisa integer as PKR display string.
     * e.g. 5000000 → "PKR 50,000"
     */
    function formatMoney(int $paisas): string
    {
        return 'PKR '.number_format($paisas / 100, 0);
    }
}

if (! function_exists('parseMoney')) {
    /**
     * Parse a PKR display string or plain number back to paisas.
     * e.g. "50,000" → 5000000
     */
    function parseMoney(string|int|float $value): int
    {
        $clean = preg_replace('/[^0-9.]/', '', (string) $value);

        return (int) round((float) $clean * 100);
    }
}
