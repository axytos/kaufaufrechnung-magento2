<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\DataMapping;

use Axytos\ECommerce\DataTransferObjects\RefundBasketTaxGroupDto;
use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;

class RefundBasketTaxGroupDtoFactory
{
    private OrderItemRepositoryInterface $orderItemRepositoryInterface;

    public function __construct(
        OrderItemRepositoryInterface $orderItemRepositoryInterface
    ) {
        $this->orderItemRepositoryInterface = $orderItemRepositoryInterface;
    }

    public function create(CreditmemoItemInterface $creditmemoItem): RefundBasketTaxGroupDto
    {
        $orderItemId = $creditmemoItem->getOrderItemId();
        $orderItem = $this->orderItemRepositoryInterface->get($orderItemId);

        $taxGroup = new RefundBasketTaxGroupDto();
        $taxGroup->total = $creditmemoItem->getTaxAmount();
        $taxGroup->valueToTax = $creditmemoItem->getPrice();
        $taxGroup->taxPercent = floatval($orderItem->getTaxPercent());

        return $taxGroup;
    }
}
