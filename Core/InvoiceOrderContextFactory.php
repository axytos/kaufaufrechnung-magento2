<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\Core;

use Axytos\ECommerce\Clients\Invoice\InvoiceOrderContextInterface;
use Axytos\KaufAufRechnung\Core\InvoiceOrderContext;
use Axytos\KaufAufRechnung\DataMapping\BasketDtoFactory;
use Axytos\KaufAufRechnung\DataMapping\CreateInvoiceBasketDtoFactory;
use Axytos\KaufAufRechnung\DataMapping\CustomerDataDtoFactory;
use Axytos\KaufAufRechnung\DataMapping\DeliveryAddressDtoFactory;
use Axytos\KaufAufRechnung\DataMapping\InvoiceAddressDtoFactory;
use Axytos\KaufAufRechnung\DataMapping\RefundBasketDtoFactory;
use Axytos\KaufAufRechnung\DataMapping\ShippingBasketPositionDtoCollectionFactory;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class InvoiceOrderContextFactory
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepositoryInterface;
    /**
     * @var \Axytos\KaufAufRechnung\DataMapping\CustomerDataDtoFactory
     */
    private $customerDataDtoFactory;
    /**
     * @var \Axytos\KaufAufRechnung\DataMapping\InvoiceAddressDtoFactory
     */
    private $invoiceAddressDtoFactory;
    /**
     * @var \Axytos\KaufAufRechnung\DataMapping\DeliveryAddressDtoFactory
     */
    private $deliveryAddressDtoFactoy;
    /**
     * @var \Axytos\KaufAufRechnung\DataMapping\BasketDtoFactory
     */
    private $basketDtoFactory;
    /**
     * @var \Axytos\KaufAufRechnung\DataMapping\RefundBasketDtoFactory
     */
    private $refundBasketDtoFactory;
    /**
     * @var \Axytos\KaufAufRechnung\DataMapping\CreateInvoiceBasketDtoFactory
     */
    private $createInvoiceBasketDtoFactory;
    /**
     * @var \Axytos\KaufAufRechnung\DataMapping\ShippingBasketPositionDtoCollectionFactory
     */
    private $shippingBasketPositionDtoCollectionFactory;

    public function __construct(
        OrderRepositoryInterface $orderRepositoryInterface,
        CustomerDataDtoFactory $customerDataDtoFactory,
        InvoiceAddressDtoFactory $invoiceAddressDtoFactory,
        DeliveryAddressDtoFactory $deliveryAddressDtoFactoy,
        BasketDtoFactory $basketDtoFactory,
        RefundBasketDtoFactory $refundBasketDtoFactory,
        CreateInvoiceBasketDtoFactory $createInvoiceBasketDtoFactory,
        ShippingBasketPositionDtoCollectionFactory $shippingBasketPositionDtoCollectionFactory
    ) {
        $this->orderRepositoryInterface = $orderRepositoryInterface;
        $this->customerDataDtoFactory = $customerDataDtoFactory;
        $this->invoiceAddressDtoFactory = $invoiceAddressDtoFactory;
        $this->deliveryAddressDtoFactoy = $deliveryAddressDtoFactoy;
        $this->basketDtoFactory = $basketDtoFactory;
        $this->refundBasketDtoFactory = $refundBasketDtoFactory;
        $this->createInvoiceBasketDtoFactory = $createInvoiceBasketDtoFactory;
        $this->shippingBasketPositionDtoCollectionFactory = $shippingBasketPositionDtoCollectionFactory;
    }

    public function getInvoiceOrderContext(
        OrderInterface $order,
        ?ShipmentInterface $shipment = null,
        ?CreditmemoInterface $creditmemo = null,
        ?InvoiceInterface $invoice = null
    ): InvoiceOrderContextInterface {
        return new InvoiceOrderContext(
            $order,
            $shipment,
            $creditmemo,
            $invoice,
            $this->orderRepositoryInterface,
            $this->customerDataDtoFactory,
            $this->invoiceAddressDtoFactory,
            $this->deliveryAddressDtoFactoy,
            $this->basketDtoFactory,
            $this->refundBasketDtoFactory,
            $this->createInvoiceBasketDtoFactory,
            $this->shippingBasketPositionDtoCollectionFactory
        );
    }
}
