<?php

namespace Tests\Unit;

use App\Importe;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class ImporteTest extends TestCase
{
    #[TestWith(['0.01', 1])]
    #[TestWith(['8.50', 850])]
    #[TestWith(['12', 1200])]
    #[TestWith(['999999.99', 99999999])]
    public function test_decimal_prices_are_converted_to_exact_cents(string $amount, int $cents): void
    {
        $this->assertSame($cents, Importe::aCentavos($amount));
    }

    public function test_subtotals_are_formatted_without_floating_point_arithmetic(): void
    {
        $this->assertSame('0.30', Importe::aDecimal(Importe::aCentavos('0.10') * 3));
        $this->assertSame('0.00', Importe::aDecimal(0));
        $this->assertSame('17.00', Importe::aDecimal(1700));
    }

    #[TestWith(['1.005'])]
    #[TestWith(['-1.00'])]
    #[TestWith(['1e2'])]
    public function test_invalid_prices_are_rejected_instead_of_silently_rounded(string $amount): void
    {
        $this->expectException(InvalidArgumentException::class);

        Importe::aCentavos($amount);
    }
}
