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
    /**
     * @var \Magento\Sales\Api\OrderItemRepositoryInterface
     */
    private $orderItemRepositoryInterface;
    /**
     * @var \Axytos\KaufAufRechnung\ValueCalculation\ShippingPositionTaxPercentCalculator
     */
    private $shippingPositionTaxPercentCalculator;

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

        $taxGroup->total = floatval($creditmemoItem->getTaxAmount());
        $taxGroup->valueToTax = floatval($creditmemoItem->getPrice());
        $taxGroup->taxPercent = floatval($orderItem->getTaxPercent());

        return $taxGroup;
    }

    public function createShippingPosition(CreditmemoInterface $creditmemo): RefundBasketTaxGroupDto
    {
        /** @var float */
        $shippingTaxAmount = floatval($creditmemo->getShippingTaxAmount());
        /** @var float */
        $shippingAmount = floatval($creditmemo->getShippingAmount());

        $taxGroup = new RefundBasketTaxGroupDto();
        $taxGroup->total = floatval($creditmemo->getShippingTaxAmount());
        $taxGroup->valueToTax = floatval($creditmemo->getShippingAmount());
        $taxGroup->taxPercent = floatval($this->shippingPositionTaxPercentCalculator->calculate($shippingTaxAmount, $shippingAmount));

        return $taxGroup;
    }
}
