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
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class InvoiceOrderContext implements InvoiceOrderContextInterface
{
    private OrderInterface $order;
    private ?ShipmentInterface $shipment;
    private ?CreditmemoInterface $creditmemo;
    private ?InvoiceInterface $invoice;

    private OrderRepositoryInterface $orderRepository;
    private CustomerDataDtoFactory $customerDataDtoFactory;
    private InvoiceAddressDtoFactory $invoiceAddressDtoFactory;
    private DeliveryAddressDtoFactory $deliveryAddressDtoFactoy;
    private BasketDtoFactory $basketDtoFactory;
    private RefundBasketDtoFactory $refundBasketDtoFactory;
    private CreateInvoiceBasketDtoFactory $createInvoiceBasketDtoFactory;
    private ShippingBasketPositionDtoCollectionFactory $shippingBasketPositionDtoCollectionFactory;

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
        ShippingBasketPositionDtoCollectionFactory $shippingBasketPositionDtoCollectionFactory
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
    }

    public function getOrderNumber(): string
    {
        return strval($this->order->getIncrementId());
    }

    public function getOrderInvoiceNumber(): string
    {
        /** @phpstan-ignore-next-line */
        return strval($this->invoice->getIncrementId());
    }

    public function setOrderInvoiceNumber(string $invoiceNumber): void
    {
    }

    public function getOrderDateTime(): DateTimeInterface
    {
        $createdAt = $this->order->getCreatedAt();
        if (is_null($createdAt)) {
            return new DateTime();
        }
        return new DateTime($createdAt);
    }

    public function getPersonalData(): CustomerDataDto
    {
        return $this->customerDataDtoFactory->create($this->order);
    }

    public function getInvoiceAddress(): InvoiceAddressDto
    {
        return $this->invoiceAddressDtoFactory->create($this->order);
    }

    public function getDeliveryAddress(): DeliveryAddressDto
    {
        return $this->deliveryAddressDtoFactoy->create($this->order);
    }

    public function getBasket(): BasketDto
    {
        return $this->basketDtoFactory->create($this->order);
    }

    public function getRefundBasket(): RefundBasketDto
    {
        if (is_null($this->creditmemo)) {
            return new RefundBasketDto();
        }
        return $this->refundBasketDtoFactory->create($this->creditmemo);
    }

    public function getCreateInvoiceBasket(): CreateInvoiceBasketDto
    {
        if (is_null($this->invoice)) {
            return new CreateInvoiceBasketDto();
        }
        return $this->createInvoiceBasketDtoFactory->create($this->invoice);
    }

    public function getShippingBasketPositions(): ShippingBasketPositionDtoCollection
    {
        if (is_null($this->shipment)) {
            return new ShippingBasketPositionDtoCollection();
        }
        return $this->shippingBasketPositionDtoCollectionFactory->create($this->shipment);
    }

    public function getReturnPositions(): ReturnPositionModelDtoCollection
    {
        return new ReturnPositionModelDtoCollection();
    }

    public function getPreCheckResponseData(): array
    {
        /** @phpstan-ignore-next-line because extension interface is generated by magento2 */
        $preCheckResponse = $this->order->getExtensionAttributes()->getAxytosKaufaufrechnungPrecheckResult();
        if (is_null($preCheckResponse)) {
            return [];
        }
        return $preCheckResponse;
    }

    public function setPreCheckResponseData(array $data): void
    {
        $attributes = $this->order->getExtensionAttributes();

        if (is_null($attributes)) {
            return;
        }

        /** @phpstan-ignore-next-line because extension interface is generated by magento2 */
        $attributes->setAxytosKaufaufrechnungPrecheckResult($data);
        $this->order->setExtensionAttributes($attributes);

        $this->orderRepository->save($this->order);
    }
}
