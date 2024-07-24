<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\DataMapping;

use Axytos\ECommerce\DataTransferObjects\ShippingBasketPositionDto;
use Axytos\KaufAufRechnung\ProductInformation\ProductInformationInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentItemInterface;

class ShippingBasketPositionDtoFactory
{
    public function create(ShipmentItemInterface $shippingItem, ProductInformationInterface $productInformation): ShippingBasketPositionDto
    {
        $position = new ShippingBasketPositionDto();
        $position->productId = $productInformation->getSku();
        $position->quantity = intval($shippingItem->getQty());
        return $position;
    }

    public function createShippingPosition(): ShippingBasketPositionDto
    {
        $position = new ShippingBasketPositionDto();
        $position->productId = '0';
        $position->quantity = 1;
        return $position;
    }
}
