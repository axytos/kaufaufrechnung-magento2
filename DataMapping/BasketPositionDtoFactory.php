<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\DataMapping;

use Axytos\ECommerce\DataTransferObjects\BasketPositionDto;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;

class BasketPositionDtoFactory
{
    public function create(OrderItemInterface $orderItem): BasketPositionDto
    {
        $position = new BasketPositionDto();
        $position->productId = strval($orderItem->getProductId());
        $position->productName = $orderItem->getName();
        $position->productCategory = $orderItem->getProductType();
        $position->quantity = intval($orderItem->getQtyOrdered());
        $position->taxPercent = floatval($orderItem->getTaxPercent());
        $position->netPricePerUnit = floatval($orderItem->getPrice());
        $position->grossPricePerUnit = floatval($orderItem->getPriceInclTax());
        $position->netPositionTotal = $position->quantity * $position->netPricePerUnit;
        $position->grossPositionTotal = $position->quantity * $position->grossPricePerUnit;
        return $position;
    }

    public function createShippingPosition(OrderInterface $order): BasketPositionDto
    {
        $position = new BasketPositionDto();
        $position->productId = '0';
        $position->productName = 'Shipping';
        $position->quantity = 1;
        $position->taxPercent = floatval($order->getShippingTaxAmount());
        $position->netPricePerUnit = floatval($order->getShippingAmount());
        $position->grossPricePerUnit = floatval($order->getShippingInclTax());
        $position->netPositionTotal = $position->quantity * $position->netPricePerUnit;
        $position->grossPositionTotal = $position->quantity * $position->grossPricePerUnit;
        return $position;
    }
}
