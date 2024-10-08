<?php

namespace Axytos\KaufAufRechnung\Adapter;

use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Model\Order;

class MagentoSalesOrder
{
    /**
     * @var Order
     */
    private $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * @return OrderInterface
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return ShipmentInterface|null
     */
    public function getShipment()
    {
        $collection = $this->order->getShipmentsCollection();

        if (is_null($collection) || false === $collection) {
            return null;
        }

        /** @var array<ShipmentInterface> */
        $items = array_values($collection->getItems());

        if (0 === count($items)) {
            return null;
        }

        return $items[0];
    }

    /**
     * @return CreditmemoInterface|null
     */
    public function getCreditmemo()
    {
        $collection = $this->order->getCreditmemosCollection();

        if (is_null($collection) || false === $collection) {
            return null;
        }

        /** @var array<CreditmemoInterface> */
        $items = array_values($collection->getItems());

        if (0 === count($items)) {
            return null;
        }

        return $items[0];
    }

    /**
     * @return InvoiceInterface|null
     */
    public function getInvoice()
    {
        $collection = $this->order->getInvoiceCollection();

        if (is_null($collection) || false === $collection) {
            return null;
        }

        /** @var array<InvoiceInterface> */
        $items = array_values($collection->getItems());

        if (0 === count($items)) {
            return null;
        }

        return $items[0];
    }
}
