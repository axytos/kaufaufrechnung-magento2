<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\DataMapping;

use Axytos\ECommerce\DataTransferObjects\BasketPositionDtoCollection;
use Magento\Sales\Api\Data\OrderInterface;

class BasketPositionDtoCollectionFactory
{
    private BasketPositionDtoFactory $basketPositionDtoFactory;

    public function __construct(BasketPositionDtoFactory $basketPositionDtoFactory)
    {
        $this->basketPositionDtoFactory = $basketPositionDtoFactory;
    }

    public function create(OrderInterface $order): BasketPositionDtoCollection
    {
        $positions = array_map([$this->basketPositionDtoFactory, 'create'], $order->getItems());
        $positions = array_values($positions);

        array_push($positions, $this->basketPositionDtoFactory->createShippingPosition($order));

        return new BasketPositionDtoCollection(...$positions);
    }
}
