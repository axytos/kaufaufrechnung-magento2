<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\ValueCalculation;

class ShippingPositionTaxPercentCalculator
{
    public function calculate(float $shippingTaxAmount, float $shippingAmount): float
    {
        if (0.0 === $shippingAmount) {
            return 0.0;
        }

        return round(($shippingTaxAmount / $shippingAmount) * 100, 2);
    }
}
