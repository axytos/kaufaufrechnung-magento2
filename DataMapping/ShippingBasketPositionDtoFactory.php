<?php declare(strict_types=1);

namespace Axytos\KaufAufRechnung\DataMapping;

use Axytos\ECommerce\DataTransferObjects\ShippingBasketPositionDto;
use Magento\Sales\Api\Data\ShipmentItemInterface;

class ShippingBasketPositionDtoFactory
{
    public function create(ShipmentItemInterface $shippingItem): ShippingBasketPositionDto
    {
        $position = new ShippingBasketPositionDto();
        $position->productId = strval($shippingItem->getProductId());
        $position->quantity = intval($shippingItem->getQty());
        return $position;
    }
}