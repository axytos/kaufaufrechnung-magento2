<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\DataMapping;

use Axytos\ECommerce\DataTransferObjects\BasketPositionDto;
use Axytos\KaufAufRechnung\ProductInformation\ProductInformationInterface;
use Axytos\KaufAufRechnung\ValueCalculation\ShippingPositionTaxPercentCalculator;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;

class BasketPositionDtoFactory
{
    /**
     * @var ShippingPositionTaxPercentCalculator
     */
    private $shippingPositionTaxPercentCalculator;

    public function __construct(ShippingPositionTaxPercentCalculator $shippingPositionTaxPercentCalculator)
    {
        $this->shippingPositionTaxPercentCalculator = $shippingPositionTaxPercentCalculator;
    }

    public function create(OrderItemInterface $orderItem, ProductInformationInterface $productInformation): BasketPositionDto
    {
        // quantity can have decimal values for things sold by meter, kilogram, etc.
        // we need this value to calculate correct position totals and per unit prices
        $floatQuantity = floatval($orderItem->getQtyOrdered());

        $position = new BasketPositionDto();
        $position->productId = $productInformation->getSku();
        $position->productName = $productInformation->getName();
        $position->productCategory = $productInformation->getCategory();
        $position->quantity = floatval($floatQuantity); // api does not accept float values yet[REMOVE]
        $position->taxPercent = floatval($orderItem->getTaxPercent());
        $position->netPricePerUnit = floatval($orderItem->getPrice());
        $position->grossPricePerUnit = floatval($orderItem->getPriceInclTax());
        $position->netPositionTotal = round($floatQuantity * $position->netPricePerUnit, 2);
        $position->grossPositionTotal = round($floatQuantity * $position->grossPricePerUnit, 2);

        return $position;
    }

    public function createVoucherPosition(OrderInterface $order): BasketPositionDto
    {
        // getDscountAmount() may return negative values
        $discountAmount = floatval($order->getDiscountAmount());
        $discountAmount = $discountAmount <= 0 ? $discountAmount : -$discountAmount;

        $position = new BasketPositionDto();
        $position->productId = 'magentovoucherdiscount';
        $position->productName = 'Discount';
        $position->productCategory = 'Discount';
        $position->quantity = 1;
        $position->taxPercent = 0.0;
        $position->netPricePerUnit = 0;
        $position->grossPricePerUnit = $discountAmount;
        $position->netPositionTotal = 0;
        $position->grossPositionTotal = $position->grossPricePerUnit;

        return $position;
    }

    public function createShippingPosition(OrderInterface $order): BasketPositionDto
    {
        $position = new BasketPositionDto();
        $position->productId = '0';
        $position->productName = 'Shipping';
        $position->quantity = 1;
        $position->taxPercent = $this->shippingPositionTaxPercentCalculator->calculate(floatval($order->getShippingTaxAmount()), floatval($order->getShippingAmount()));
        $position->netPricePerUnit = floatval($order->getShippingAmount());
        $position->grossPricePerUnit = floatval($order->getShippingInclTax());
        $position->netPositionTotal = round($position->quantity * $position->netPricePerUnit, 2);
        $position->grossPositionTotal = round($position->quantity * $position->grossPricePerUnit, 2);

        return $position;
    }
}
