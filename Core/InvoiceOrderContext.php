<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\Core;

use Axytos\ECommerce\Clients\Invoice\InvoiceOrderContextInterface;
use Axytos\ECommerce\DataTransferObjects\BasketDto;
use Axytos\ECommerce\DataTransferObjects\CreateInvoiceBasketDto;
use Axytos\ECommerce\DataTransferObjects\CustomerDataDto;
use Axytos\ECommerce\DataTransferObjects\DeliveryAddressDto;
use Axytos\ECommerce\DataTransferObjects\InvoiceAddressDto;
use Axytos\ECommerce\DataTransferObjects\RefundBasketDto;
use Axytos\ECommerce\DataTransferObjects\ReturnPositionModelDtoCollection;
use Axytos\ECommerce\DataTransferObjects\ShippingBasketPositionDtoCollection;
use Axytos\KaufAufRechnung\DataMapping\BasketDtoFactory;
use Axytos\KaufAufRechnung\DataMapping\CreateInvoiceBasketDtoFactory;
use Axytos\KaufAufRechnung\DataMapping\CustomerDataDtoFactory;
use Axytos\KaufAufRechnung\DataMapping\DeliveryAddressDtoFactory;
use Axytos\KaufAufRechnung\DataMapping\InvoiceAddressDtoFactory;
use Axytos\KaufAufRechnung\DataMapping\RefundBasketDtoFactory;
use Axytos\KaufAufRechnung\DataMapping\ShippingBasketPositionDtoCollectionFactory;
use DateTime;
use DateTimeInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class InvoiceOrderContext implements InvoiceOrderContextInterface
{
    /**
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    private $order;
    /**
     * @var \Magento\Sales\Api\Data\ShipmentInterface|null
     */
    private $shipment;
    /**
     * @var \Magento\Sales\Api\Data\CreditmemoInterface|null
     */
    private $creditmemo;
    /**
     * @var \Magento\Sales\Api\Data\InvoiceInterface|null
     */
    private $invoice;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;
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

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    public function __construct(
        OrderInterface $order,
        ?ShipmentInterface $shipment,
        ?CreditmemoInterface $creditmemo,
        ?InvoiceInterface $invoice,
        OrderRepositoryInterface $orderRepository,
        CustomerDataDtoFactory $customerDataDtoFactory,
        InvoiceAddressDtoFactory $invoiceAddressDtoFactory,
        DeliveryAddressDtoFactory $deliveryAddressDtoFactoy,
        BasketDtoFactory $basketDtoFactory,
        RefundBasketDtoFactory $refundBasketDtoFactory,
        CreateInvoiceBasketDtoFactory $createInvoiceBasketDtoFactory,
        ShippingBasketPositionDtoCollectionFactory $shippingBasketPositionDtoCollectionFactory,
        SerializerInterface $serializer
    ) {
        $this->order = $order;
        $this->shipment = $shipment;
        $this->creditmemo = $creditmemo;
        $this->invoice = $invoice;
        $this->orderRepository = $orderRepository;
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
     * @return string
     */
    public function getOrderNumber()
    {
        return strval($this->order->getIncrementId());
    }

    /**
     * @return string
     */
    public function getOrderInvoiceNumber()
    {
        /** @phpstan-ignore-next-line */
        return strval($this->invoice->getIncrementId());
    }

    /**
     * @param string $invoiceNumber
     * @return void
     */
    public function setOrderInvoiceNumber(string $invoiceNumber)
    {
    }

    /**
     * @return DateTimeInterface
     */
    public function getOrderDateTime()
    {
        $createdAt = $this->order->getCreatedAt();
        if (is_null($createdAt)) {
            return new DateTime();
        }
        return new DateTime($createdAt);
    }

    /**
     * @return CustomerDataDto
     */
    public function getPersonalData()
    {
        return $this->customerDataDtoFactory->create($this->order);
    }

    /**
     * @return InvoiceAddressDto
     */
    public function getInvoiceAddress()
    {
        return $this->invoiceAddressDtoFactory->create($this->order);
    }

    /**
     * @return DeliveryAddressDto
     */
    public function getDeliveryAddress()
    {
        return $this->deliveryAddressDtoFactoy->create($this->order);
    }

    /**
     * @return BasketDto
     */
    public function getBasket()
    {
        return $this->basketDtoFactory->create($this->order);
    }

    /**
     * @return RefundBasketDto
     */
    public function getRefundBasket()
    {
        if (is_null($this->creditmemo)) {
            return new RefundBasketDto();
        }
        return $this->refundBasketDtoFactory->create($this->creditmemo);
    }

    /**
     * @return CreateInvoiceBasketDto
     */
    public function getCreateInvoiceBasket()
    {
        if (is_null($this->invoice)) {
            return new CreateInvoiceBasketDto();
        }
        return $this->createInvoiceBasketDtoFactory->create($this->invoice);
    }

    /**
     * @return ShippingBasketPositionDtoCollection
     */
    public function getShippingBasketPositions()
    {
        if (is_null($this->shipment)) {
            return new ShippingBasketPositionDtoCollection();
        }
        return $this->shippingBasketPositionDtoCollectionFactory->create($this->shipment);
    }

    /**
     * @return ReturnPositionModelDtoCollection
     */
    public function getReturnPositions()
    {
        return new ReturnPositionModelDtoCollection();
    }

    /**
     * @return array<mixed>
     */
    public function getPreCheckResponseData()
    {
        $extensionAttributes = $this->order->getExtensionAttributes();
        if (is_null($extensionAttributes)) {
            return [];
        }

        /**
         * @phpstan-ignore-next-line because extension interface is generated by magento2
         * @var \Axytos\KaufAufRechnung\Model\Data\AxytosOrderAttributesInterface */
        $axytosOrderAttributes = $extensionAttributes->getAxytosKaufaufrechnungOrderAttributes();
        if (is_null($axytosOrderAttributes)) {
            return [];
        }

        $preCheckResponse = strval($axytosOrderAttributes->getOrderPreCheckResult());
        if ($preCheckResponse === '') {
            return [];
        }

        $preCheckResponse = $this->serializer->unserialize($preCheckResponse);
        if (!is_array($preCheckResponse)) {
            return [];
        }

        return $preCheckResponse;
    }

    /**
     * @param array<mixed> $data
     * @return void
     */
    public function setPreCheckResponseData($data)
    {
        $extensionAttributes = $this->order->getExtensionAttributes();
        if (is_null($extensionAttributes)) {
            return;
        }

        /**
         * @phpstan-ignore-next-line because extension interface is generated by magento2
         * @var \Axytos\KaufAufRechnung\Model\Data\AxytosOrderAttributesInterface */
        $axytosOrderAttributes = $extensionAttributes->getAxytosKaufaufrechnungOrderAttributes();
        if (is_null($axytosOrderAttributes)) {
            return;
        }

        $axytosOrderAttributes->setOrderPreCheckResult(strval($this->serializer->serialize($data)));
        $this->order->setExtensionAttributes($extensionAttributes);

        $this->orderRepository->save($this->order);
    }

    /**
     * @return float
     */
    public function getDeliveryWeight()
    {
        // not yet supported for magento2

        // for now delivery weight is not important for risk evaluation
        // because different shop systems don't always provide the necessary
        // information to accurately the exact delivery weight for each delivery
        // we decided to return 0 as constant delivery weight
        return 0;
    }

    /**
     * @return string[]
     */
    public function getTrackingIds()
    {
        // not yet supported for magento2
        return [];
    }

    /**
     * @return string
     */
    public function getLogistician()
    {
        // not yet supported for magento2
        return '';
    }
}
