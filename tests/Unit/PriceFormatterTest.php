<?php

namespace Tests\Unit;

use App\Models\PropertyOperation;
use App\Support\PriceFormatter;
use PHPUnit\Framework\TestCase;

class PriceFormatterTest extends TestCase
{
    public function test_formats_mxn_without_cents(): void
    {
        $this->assertSame('$12,345,678 MXN', PriceFormatter::format(12345678, 'MXN'));
    }

    public function test_formats_usd_with_cents(): void
    {
        $this->assertSame('$650,000.90 USD', PriceFormatter::format(650000.90, 'USD'));
    }

    public function test_extracts_numeric_amount_from_localized_string(): void
    {
        $this->assertSame(12345678.90, PriceFormatter::extractNumericAmount('$12,345,678.90 MXN'));
    }

    public function test_extracts_numeric_amount_from_legacy_dot_thousands_format(): void
    {
        $this->assertSame(420000000.00, PriceFormatter::extractNumericAmount('$ 420.000.000'));
    }

    public function test_appends_currency_suffix_when_missing(): void
    {
        $this->assertSame('$499,000 MXN', PriceFormatter::ensureCurrencySuffix('$499,000', 'MXN'));
    }

    public function test_property_operation_accessor_returns_standardized_formatted_amount(): void
    {
        $operation = new PropertyOperation();
        $operation->amount = '8950000.00';
        $operation->currency_code = 'MXN';

        $this->assertSame('$8,950,000 MXN', $operation->formatted_amount);
    }
}
