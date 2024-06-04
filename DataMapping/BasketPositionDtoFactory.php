<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\DataMapping;

use Axytos\ECommerce\DataTransferObjects\BasketPositionDto;
use Axytos\KaufAufRechnung\ValueCalculation\ShippingPositionTaxPercentCalculator;
use AxytosKaufAufRechnungShopware5\Adapter\Common\BasketPosition;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;

class BasketPositionDtoFactory
{
    /**
     * @var \Axytos\KaufAufRechnung\ValueCalculation\ShippingPositionTaxPercentCalculator
     */
    private $shippingPositionTaxPercentCalculator;

    public function __construct(ShippingPositionTaxPercentCalculator $shippingPositionTaxPercentCalculator)
    {
        $this->shippingPositionTaxPercentCalculator = $shippingPositionTaxPercentCalculator;
    }

    public function create(OrderItemInterface $orderItem): BasketPositionDto
    {
        $position = new BasketPositionDto();
        $position->productId = strval($orderItem->getSku());
        $position->productName = $orderItem->getName();
        $position->productCategory = $orderItem->getProductType();
        $position->quantity = intval($orderItem->getQtyOrdered());
        $position->taxPercent = floatval($orderItem->getTaxPercent());
        $position->netPricePerUnit = floatval($orderItem->getPrice());
        $position->grossPricePerUnit = floatval($orderItem->getPriceInclTax());
        $position->netPositionTotal = round($position->quantity * $position->netPricePerUnit, 2);
        $position->grossPositionTotal = round($position->quantity * $position->grossPricePerUnit, 2);
        return $position;
    }

    public function createVoucherPosition(OrderInterface $order): BasketPositionDto
    {
        $position = new BasketPositionDto();
        $position->productId = 'magentovoucherdiscount';
        $position->productName = 'Discount';
        $position->productCategory = 'Discount';
        $position->quantity = 1;
        $position->taxPercent = 0.0;
        $position->netPricePerUnit = 0;
        $position->grossPricePerUnit = -floatval($order->getDiscountAmount());
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
