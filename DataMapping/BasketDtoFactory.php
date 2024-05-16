<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\DataMapping;

use Axytos\ECommerce\DataTransferObjects\BasketDto;
use Magento\Sales\Api\Data\OrderInterface;

class BasketDtoFactory
{
    /**
     * @var \Axytos\KaufAufRechnung\DataMapping\BasketPositionDtoCollectionFactory
     */
    private $basketPostionDtoCollectionFactory;

    public function __construct(BasketPositionDtoCollectionFactory $basketPostionDtoCollectionFactory)
    {
        $this->basketPostionDtoCollectionFactory = $basketPostionDtoCollectionFactory;
    }

    public function create(OrderInterface $order): BasketDto
    {
        $basketDto = new BasketDto();
        $basketDto->netTotal = floatval($order->getGrandTotal()) - floatval($order->getTaxAmount());
        $basketDto->grossTotal = floatval($order->getGrandTotal());
        $basketDto->currency = $order->getOrderCurrencyCode();
        $basketDto->positions = $this->basketPostionDtoCollectionFactory->create($order);

        return $basketDto;
    }
}
