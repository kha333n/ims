<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;

class HelpersTest extends TestCase
{
    // ── formatMoney ──────────────────────────────────────────────────────────

    public function test_format_money_converts_paisas_to_pkr_string(): void
    {
        $this->assertSame('PKR 50,000', formatMoney(5000000));
    }

    public function test_format_money_handles_zero(): void
    {
        $this->assertSame('PKR 0', formatMoney(0));
    }

    public function test_format_money_handles_small_amount(): void
    {
        $this->assertSame('PKR 125', formatMoney(12500));
    }

    // ── parseMoney ───────────────────────────────────────────────────────────

    public function test_parse_money_converts_plain_number_to_paisas(): void
    {
        $this->assertSame(5000000, parseMoney('50000'));
    }

    public function test_parse_money_strips_comma_separators(): void
    {
        $this->assertSame(5000000, parseMoney('50,000'));
    }

    public function test_parse_money_strips_pkr_prefix(): void
    {
        $this->assertSame(5000000, parseMoney('PKR 50,000'));
    }

    public function test_parse_money_handles_integer_input(): void
    {
        $this->assertSame(10000, parseMoney(100));
    }

    public function test_parse_money_handles_zero(): void
    {
        $this->assertSame(0, parseMoney('0'));
    }

    // ── formatDate ───────────────────────────────────────────────────────────

    public function test_format_date_returns_dd_mon_yyyy(): void
    {
        $this->assertSame('16/Apr/2025', formatDate(Carbon::parse('2025-04-16')));
    }

    public function test_format_date_accepts_string(): void
    {
        $this->assertSame('16/Apr/2025', formatDate('2025-04-16'));
    }

    public function test_format_date_returns_empty_string_for_null(): void
    {
        $this->assertSame('', formatDate(null));
    }

    public function test_format_date_formats_different_months(): void
    {
        $this->assertSame('01/Jan/2025', formatDate('2025-01-01'));
        $this->assertSame('31/Dec/2024', formatDate('2024-12-31'));
    }
}
