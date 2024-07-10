<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\DataMapping;

use Axytos\KaufAufRechnung\ProductInformation\ProductInformationInterface;
use Axytos\KaufAufRechnung\ValueCalculation\ShippingPositionTaxPercentCalculator;
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use PHPUnit\Framework\TestCase;

class DecimalQuantityPositionsTest extends TestCase
{
    /**
     * @return void
     */
    public function test_checkout_basket_positions_with_integer_quantity()
    {
        $sut = new BasketPositionDtoFactory(new ShippingPositionTaxPercentCalculator());

        $orderItem = $this->createMock(OrderItemInterface::class);
        $productInfo = $this->createMock(ProductInformationInterface::class);

        $orderItem->method('getQtyOrdered')->willReturn(2);
        $orderItem->method('getTaxPercent')->willReturn(19.00);
        $orderItem->method('getPrice')->willReturn(10.00); // net price per unit
        $orderItem->method('getPriceInclTax')->willReturn(11.90); // gross price per unit

        $position = $sut->create($orderItem, $productInfo);

        $this->assertEquals(2, $position->quantity); // api does not support decimal quantities yet
        $this->assertEquals(19.00, $position->taxPercent);
        $this->assertEquals(10.00, $position->netPricePerUnit);
        $this->assertEquals(20.00, $position->netPositionTotal);
        $this->assertEquals(11.90, $position->grossPricePerUnit);
        $this->assertEquals(23.80, $position->grossPositionTotal);
    }

    /**
     * @return void
     */
    public function test_checkout_basket_positions_with_decimal_quantity()
    {
        $sut = new BasketPositionDtoFactory(new ShippingPositionTaxPercentCalculator());

        $orderItem = $this->createMock(OrderItemInterface::class);
        $productInfo = $this->createMock(ProductInformationInterface::class);

        $orderItem->method('getQtyOrdered')->willReturn(1.77);
        $orderItem->method('getTaxPercent')->willReturn(19.00);
        $orderItem->method('getPrice')->willReturn(10.00); // net price per unit
        $orderItem->method('getPriceInclTax')->willReturn(11.90); // gross price per unit

        $position = $sut->create($orderItem, $productInfo);

        $this->assertEquals(1, $position->quantity); // api does not support decimal quantities yet
        $this->assertEquals(19.00, $position->taxPercent);
        $this->assertEquals(10.00, $position->netPricePerUnit);
        $this->assertEquals(17.70, $position->netPositionTotal);
        $this->assertEquals(11.90, $position->grossPricePerUnit);
        $this->assertEquals(21.06, $position->grossPositionTotal);
    }

    /**
     * @return void
     */
    public function test_invoice_basket_positions_with_integer_quantity()
    {
        $orderItemRepository = $this->createMock(OrderItemRepositoryInterface::class);
        $sut = new CreateInvoiceBasketPositionDtoFactory(
            $orderItemRepository,
            new ShippingPositionTaxPercentCalculator()
        );

        $orderItem = $this->createMock(OrderItemInterface::class);
        $invoiceItem = $this->createMock(InvoiceItemInterface::class);
        $productInfo = $this->createMock(ProductInformationInterface::class);

        $orderItem->method('getTaxPercent')->willReturn(19.00);
        $orderItemRepository->method('get')->willReturn($orderItem);

        $invoiceItem->method('getQty')->willReturn(2);
        $invoiceItem->method('getPrice')->willReturn(10.00); // net price per unit
        $invoiceItem->method('getPriceInclTax')->willReturn(11.90); // gross price per unit

        $position = $sut->create($invoiceItem, $productInfo);

        $this->assertEquals(2, $position->quantity); // api does not support decimal quantities yet
        $this->assertEquals(19.00, $position->taxPercent);
        $this->assertEquals(10.00, $position->netPricePerUnit);
        $this->assertEquals(20.00, $position->netPositionTotal);
        $this->assertEquals(11.90, $position->grossPricePerUnit);
        $this->assertEquals(23.80, $position->grossPositionTotal);
    }

    /**
     * @return void
     */
    public function test_invoice_basket_positions_with_decimal_quantity()
    {
        $orderItemRepository = $this->createMock(OrderItemRepositoryInterface::class);
        $sut = new CreateInvoiceBasketPositionDtoFactory(
            $orderItemRepository,
            new ShippingPositionTaxPercentCalculator()
        );

        $orderItem = $this->createMock(OrderItemInterface::class);
        $invoiceItem = $this->createMock(InvoiceItemInterface::class);
        $productInfo = $this->createMock(ProductInformationInterface::class);

        $orderItem->method('getTaxPercent')->willReturn(19.00);
        $orderItemRepository->method('get')->willReturn($orderItem);

        $invoiceItem->method('getQty')->willReturn(1.77);
        $invoiceItem->method('getPrice')->willReturn(10.00); // net price per unit
        $invoiceItem->method('getPriceInclTax')->willReturn(11.90); // gross price per unit

        $position = $sut->create($invoiceItem, $productInfo);

        $this->assertEquals(1, $position->quantity); // api does not support decimal quantities yet
        $this->assertEquals(19.00, $position->taxPercent);
        $this->assertEquals(10.00, $position->netPricePerUnit);
        $this->assertEquals(17.70, $position->netPositionTotal);
        $this->assertEquals(11.90, $position->grossPricePerUnit);
        $this->assertEquals(21.06, $position->grossPositionTotal);
    }
}
