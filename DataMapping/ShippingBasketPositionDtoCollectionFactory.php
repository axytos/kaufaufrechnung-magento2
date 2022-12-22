<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\DataMapping;

use Axytos\ECommerce\DataTransferObjects\ShippingBasketPositionDtoCollection;
use Magento\Sales\Api\Data\ShipmentInterface;

class ShippingBasketPositionDtoCollectionFactory
{
    /**
     * @var \Axytos\KaufAufRechnung\DataMapping\ShippingBasketPositionDtoFactory
     */
    private $shippingBasketPositionDtoFactory;

    public function __construct(ShippingBasketPositionDtoFactory $shippingBasketPositionDtoFactory)
    {
        $this->shippingBasketPositionDtoFactory = $shippingBasketPositionDtoFactory;
    }

    public function create(ShipmentInterface $shipment): ShippingBasketPositionDtoCollection
    {
        $positions = array_map([$this->shippingBasketPositionDtoFactory, 'create'], $shipment->getItems());
        $shippingPosition = $this->shippingBasketPositionDtoFactory->createShippingPosition();
        array_push($positions, $shippingPosition);

        return new ShippingBasketPositionDtoCollection(...$positions);
    }
}
