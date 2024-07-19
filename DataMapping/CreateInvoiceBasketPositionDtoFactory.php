<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\DataMapping;

use Axytos\ECommerce\DataTransferObjects\CreateInvoiceBasketPositionDto;
use Axytos\KaufAufRechnung\ProductInformation\ProductInformationInterface;
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

    public function create(InvoiceItemInterface $invoiceItem, ProductInformationInterface $productInformation): CreateInvoiceBasketPositionDto
    {
        $orderItemId = $invoiceItem->getOrderItemId();
        $orderItem = $this->orderItemRepositoryInterface->get($orderItemId);

        // quantity can have decimal values for things sold by meter, kilogram, etc.
        // we need this value to calculate correct position totals and per unit prices
        $floatQuantity = floatval($invoiceItem->getQty());

        $position = new CreateInvoiceBasketPositionDto();
        $position->productId = $productInformation->getSku();
        $position->productName = $productInformation->getName();
        $position->quantity = intval($floatQuantity); // api does not accept float values yet
        $position->taxPercent = floatval($orderItem->getTaxPercent());
        $position->netPricePerUnit = floatval($invoiceItem->getPrice());
        $position->grossPricePerUnit = floatval($invoiceItem->getPriceInclTax());
        $position->netPositionTotal = round($floatQuantity * $position->netPricePerUnit, 2);
        $position->grossPositionTotal = round($floatQuantity * $position->grossPricePerUnit, 2);
        return $position;
    }

    public function createVoucherPosition(InvoiceInterface $order): CreateInvoiceBasketPositionDto
    {
        // getDscountAmount() may return negative values
        $discountAmount = floatval($order->getDiscountAmount());
        $discountAmount = $discountAmount <= 0 ? $discountAmount : -$discountAmount;

        $position = new CreateInvoiceBasketPositionDto();
        $position->productId = 'magentovoucherdiscount';
        $position->productName = 'Discount';
        $position->quantity = 1;
        $position->taxPercent = 0.0;
        $position->netPricePerUnit = 0;
        $position->grossPricePerUnit = $discountAmount ;
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
