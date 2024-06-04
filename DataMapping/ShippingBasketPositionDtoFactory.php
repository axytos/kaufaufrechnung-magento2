<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\DataMapping;

use Axytos\ECommerce\DataTransferObjects\ShippingBasketPositionDto;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentItemInterface;

class ShippingBasketPositionDtoFactory
{
    public function create(ShipmentItemInterface $shippingItem): ShippingBasketPositionDto
    {
        $position = new ShippingBasketPositionDto();
        $position->productId = strval($shippingItem->getSku());
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
