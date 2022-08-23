<?php declare(strict_types=1);

namespace Axytos\KaufAufRechnung\DataMapping;

use Axytos\ECommerce\DataTransferObjects\CreateInvoiceTaxGroupDto;
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;

class CreateInvoiceTaxGroupDtoFactory
{
    private OrderItemRepositoryInterface $orderItemRepositoryInterface;

    public function __construct(OrderItemRepositoryInterface $orderItemRepositoryInterface)
    {
        $this->orderItemRepositoryInterface = $orderItemRepositoryInterface;
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
}