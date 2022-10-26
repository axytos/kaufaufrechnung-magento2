<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\ValueCalculation;

class ShippingPositionTaxPercentCalculator
{
    public function calculate(float $shippingTaxAmount, float $shippingAmount): float
    {
        return ($shippingTaxAmount / $shippingAmount) * 100;
    }
}
