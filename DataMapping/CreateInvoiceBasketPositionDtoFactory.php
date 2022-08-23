<?php declare(strict_types=1);

namespace Axytos\KaufAufRechnung\DataMapping;

use Axytos\ECommerce\DataTransferObjects\CreateInvoiceBasketPositionDto;
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;

class CreateInvoiceBasketPositionDtoFactory
{
    private OrderItemRepositoryInterface $orderItemRepositoryInterface;

    public function __construct(OrderItemRepositoryInterface $orderItemRepositoryInterface)
    {
        $this->orderItemRepositoryInterface = $orderItemRepositoryInterface;
    }

    public function create(InvoiceItemInterface $invoiceItem): CreateInvoiceBasketPositionDto
    {
        $orderItemId = $invoiceItem->getOrderItemId();
        $orderItem = $this->orderItemRepositoryInterface->get($orderItemId);

        $position = new CreateInvoiceBasketPositionDto();
        $position->productId = strval($invoiceItem->getProductId());
        $position->productName = $invoiceItem->getName();
        $position->quantity = intval($invoiceItem->getQty());
        $position->taxPercent = floatval($orderItem->getTaxPercent());
        $position->netPricePerUnit = floatval($invoiceItem->getPrice());
        $position->grossPricePerUnit = floatval($invoiceItem->getPriceInclTax());
        $position->netPositionTotal = $position->quantity * $position->netPricePerUnit;
        $position->grossPositionTotal = $position->quantity * $position->grossPricePerUnit;
        return $position;
    }
}