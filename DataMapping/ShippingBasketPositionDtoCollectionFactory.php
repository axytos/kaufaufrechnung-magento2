<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\DataMapping;

use Axytos\ECommerce\DataTransferObjects\RefundBasketTaxGroupDtoCollection;
use Axytos\ECommerce\DataTransferObjects\ShippingBasketPositionDtoCollection;
use Magento\Sales\Api\Data\ShipmentInterface;

class ShippingBasketPositionDtoCollectionFactory
{
    private ShippingBasketPositionDtoFactory $shippingBasketPositionDtoFactory;

    public function __construct(ShippingBasketPositionDtoFactory $shippingBasketPositionDtoFactory)
    {
        $this->shippingBasketPositionDtoFactory = $shippingBasketPositionDtoFactory;
    }

    public function create(ShipmentInterface $shipment): ShippingBasketPositionDtoCollection
    {
        $positions = array_map([$this->shippingBasketPositionDtoFactory, 'create'], $shipment->getItems());

        return new ShippingBasketPositionDtoCollection(...$positions);
    }
}
