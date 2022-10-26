<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\DataMapping;

use Axytos\ECommerce\DataTransferObjects\RefundBasketTaxGroupDto;
use Axytos\KaufAufRechnung\ValueCalculation\ShippingPositionTaxPercentCalculator;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;

class RefundBasketTaxGroupDtoFactory
{
    private OrderItemRepositoryInterface $orderItemRepositoryInterface;
    private ShippingPositionTaxPercentCalculator $shippingPositionTaxPercentCalculator;

    public function __construct(
        OrderItemRepositoryInterface $orderItemRepositoryInterface,
        ShippingPositionTaxPercentCalculator $shippingPositionTaxPercentCalculator
    ) {
        $this->orderItemRepositoryInterface = $orderItemRepositoryInterface;
        $this->shippingPositionTaxPercentCalculator = $shippingPositionTaxPercentCalculator;
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

    public function createShippingPosition(CreditmemoInterface $creditmemo): RefundBasketTaxGroupDto
    {
        /** @var float */
        $shippingTaxAmount = $creditmemo->getShippingTaxAmount();
        /** @var float */
        $shippingAmount = $creditmemo->getShippingAmount();

        $taxGroup = new RefundBasketTaxGroupDto();
        $taxGroup->total = $creditmemo->getShippingTaxAmount();
        $taxGroup->valueToTax = $creditmemo->getShippingAmount();
        $taxGroup->taxPercent = $this->shippingPositionTaxPercentCalculator->calculate($shippingTaxAmount, $shippingAmount);

        return $taxGroup;
    }
}
