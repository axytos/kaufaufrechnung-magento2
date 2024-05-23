<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\DataMapping;

use Axytos\ECommerce\DataTransferObjects\CreateInvoiceBasketPositionDto;
use Axytos\KaufAufRechnung\ValueCalculation\ShippingPositionTaxPercentCalculator;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;

class CreateInvoiceBasketPositionDtoFactory
{
    /**
     * @var \Magento\Sales\Api\OrderItemRepositoryInterface
     */
    private $orderItemRepositoryInterface;
    /**
     * @var \Axytos\KaufAufRechnung\ValueCalculation\ShippingPositionTaxPercentCalculator
     */
    private $shippingPositionTaxPercentCalculator;

    public function __construct(OrderItemRepositoryInterface $orderItemRepositoryInterface, ShippingPositionTaxPercentCalculator $shippingPositionTaxPercentCalculator)
    {
        $this->orderItemRepositoryInterface = $orderItemRepositoryInterface;
        $this->shippingPositionTaxPercentCalculator = $shippingPositionTaxPercentCalculator;
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
        $position->netPositionTotal = round($position->quantity * $position->netPricePerUnit, 2);
        $position->grossPositionTotal = round($position->quantity * $position->grossPricePerUnit, 2);
        return $position;
    }

    public function createVoucherPosition(InvoiceInterface $order): CreateInvoiceBasketPositionDto
    {
        $position = new CreateInvoiceBasketPositionDto();
        $position->productId = 'magentovoucherdiscount';
        $position->productName = 'Discount';
        $position->quantity = 1;
        $position->taxPercent = 0.0;
        $position->netPricePerUnit = 0;
        $position->grossPricePerUnit = -floatval($order->getDiscountAmount());
        $position->netPositionTotal = 0;
        $position->grossPositionTotal = $position->grossPricePerUnit;
        return $position;
    }

    public function createShippingPosition(InvoiceInterface $invoice): CreateInvoiceBasketPositionDto
    {
        $position = new CreateInvoiceBasketPositionDto();
        $position->productId = '0';
        $position->productName = 'Shipping';
        $position->quantity = 1;
        $position->taxPercent = $this->shippingPositionTaxPercentCalculator->calculate(floatval($invoice->getShippingTaxAmount()), floatval($invoice->getShippingAmount()));
        $position->netPricePerUnit = floatval($invoice->getShippingAmount());
        $position->grossPricePerUnit = floatval($invoice->getShippingInclTax());
        $position->netPositionTotal = floatval($invoice->getShippingAmount());
        $position->grossPositionTotal = floatval($invoice->getShippingInclTax());
        return $position;
    }
}
