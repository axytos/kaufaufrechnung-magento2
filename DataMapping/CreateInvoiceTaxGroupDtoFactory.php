<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\DataMapping;

use Axytos\ECommerce\DataTransferObjects\CreateInvoiceTaxGroupDto;
use Axytos\KaufAufRechnung\ValueCalculation\ShippingPositionTaxPercentCalculator;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;

class CreateInvoiceTaxGroupDtoFactory
{
    /**
     * @var OrderItemRepositoryInterface
     */
    private $orderItemRepositoryInterface;
    /**
     * @var ShippingPositionTaxPercentCalculator
     */
    private $shippingPositionTaxPercentCalculator;

    public function __construct(OrderItemRepositoryInterface $orderItemRepositoryInterface, ShippingPositionTaxPercentCalculator $shippingPositionTaxPercentCalculator)
    {
        $this->orderItemRepositoryInterface = $orderItemRepositoryInterface;
        $this->shippingPositionTaxPercentCalculator = $shippingPositionTaxPercentCalculator;
    }

    public function create(InvoiceItemInterface $invoiceItem): CreateInvoiceTaxGroupDto
    {
        $orderItemId = $invoiceItem->getOrderItemId();
        $orderItem = $this->orderItemRepositoryInterface->get($orderItemId);

        $taxGroup = new CreateInvoiceTaxGroupDto();
        $taxGroup->total = floatval($invoiceItem->getTaxAmount());
        $taxGroup->valueToTax = floatval($invoiceItem->getPrice());
        $taxGroup->taxPercent = floatval($orderItem->getTaxPercent());

        return $taxGroup;
    }

    public function createShippingPosition(InvoiceInterface $invoice): CreateInvoiceTaxGroupDto
    {
        $taxGroup = new CreateInvoiceTaxGroupDto();
        $taxGroup->total = floatval($invoice->getShippingInclTax());
        $taxGroup->valueToTax = floatval($invoice->getShippingAmount());
        $taxGroup->taxPercent = $this->shippingPositionTaxPercentCalculator->calculate(floatval($invoice->getShippingTaxAmount()), floatval($invoice->getShippingAmount()));

        return $taxGroup;
    }
}
