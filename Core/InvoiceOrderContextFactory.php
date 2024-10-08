<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\Core;

use Axytos\ECommerce\Clients\Invoice\InvoiceOrderContextInterface;
use Axytos\KaufAufRechnung\DataMapping\BasketDtoFactory;
use Axytos\KaufAufRechnung\DataMapping\CreateInvoiceBasketDtoFactory;
use Axytos\KaufAufRechnung\DataMapping\CustomerDataDtoFactory;
use Axytos\KaufAufRechnung\DataMapping\DeliveryAddressDtoFactory;
use Axytos\KaufAufRechnung\DataMapping\InvoiceAddressDtoFactory;
use Axytos\KaufAufRechnung\DataMapping\RefundBasketDtoFactory;
use Axytos\KaufAufRechnung\DataMapping\ShippingBasketPositionDtoCollectionFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class InvoiceOrderContextFactory
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepositoryInterface;
    /**
     * @var CustomerDataDtoFactory
     */
    private $customerDataDtoFactory;
    /**
     * @var InvoiceAddressDtoFactory
     */
    private $invoiceAddressDtoFactory;
    /**
     * @var DeliveryAddressDtoFactory
     */
    private $deliveryAddressDtoFactoy;
    /**
     * @var BasketDtoFactory
     */
    private $basketDtoFactory;
    /**
     * @var RefundBasketDtoFactory
     */
    private $refundBasketDtoFactory;
    /**
     * @var CreateInvoiceBasketDtoFactory
     */
    private $createInvoiceBasketDtoFactory;
    /**
     * @var ShippingBasketPositionDtoCollectionFactory
     */
    private $shippingBasketPositionDtoCollectionFactory;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(
        OrderRepositoryInterface $orderRepositoryInterface,
        CustomerDataDtoFactory $customerDataDtoFactory,
        InvoiceAddressDtoFactory $invoiceAddressDtoFactory,
        DeliveryAddressDtoFactory $deliveryAddressDtoFactoy,
        BasketDtoFactory $basketDtoFactory,
        RefundBasketDtoFactory $refundBasketDtoFactory,
        CreateInvoiceBasketDtoFactory $createInvoiceBasketDtoFactory,
        ShippingBasketPositionDtoCollectionFactory $shippingBasketPositionDtoCollectionFactory,
        SerializerInterface $serializer
    ) {
        $this->orderRepositoryInterface = $orderRepositoryInterface;
        $this->customerDataDtoFactory = $customerDataDtoFactory;
        $this->invoiceAddressDtoFactory = $invoiceAddressDtoFactory;
        $this->deliveryAddressDtoFactoy = $deliveryAddressDtoFactoy;
        $this->basketDtoFactory = $basketDtoFactory;
        $this->refundBasketDtoFactory = $refundBasketDtoFactory;
        $this->createInvoiceBasketDtoFactory = $createInvoiceBasketDtoFactory;
        $this->shippingBasketPositionDtoCollectionFactory = $shippingBasketPositionDtoCollectionFactory;
        $this->serializer = $serializer;
    }

    /**
     * @return \Axytos\ECommerce\Clients\Invoice\InvoiceOrderContextInterface&\Axytos\KaufAufRechnung\Core\InvoiceOrderContext
     */
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
            $this->shippingBasketPositionDtoCollectionFactory,
            $this->serializer
        );
    }
}
