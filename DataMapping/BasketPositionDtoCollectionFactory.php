<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\DataMapping;

use Axytos\ECommerce\DataTransferObjects\BasketPositionDtoCollection;
use Magento\Sales\Api\Data\OrderInterface;

class BasketPositionDtoCollectionFactory
{
    /**
     * @var \Axytos\KaufAufRechnung\DataMapping\BasketPositionDtoFactory
     */
    private $basketPositionDtoFactory;

    public function __construct(BasketPositionDtoFactory $basketPositionDtoFactory)
    {
        $this->basketPositionDtoFactory = $basketPositionDtoFactory;
    }

    public function create(OrderInterface $order): BasketPositionDtoCollection
    {
        $positions = array_map([$this->basketPositionDtoFactory, 'create'], $order->getItems());
        $positions = array_values($positions);

        $voucherPosition = $this->basketPositionDtoFactory->createVoucherPosition($order);
        if (!is_null($voucherPosition)) {
            if ($voucherPosition->grossPositionTotal !== 0.0) {
                array_push($positions, $voucherPosition);
            }
        }

        array_push($positions, $this->basketPositionDtoFactory->createShippingPosition($order));

        return new BasketPositionDtoCollection(...$positions);
    }
}
