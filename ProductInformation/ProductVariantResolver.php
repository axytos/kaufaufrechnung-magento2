<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\ProductInformation;

use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\Data\ShipmentItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;

class ProductVariantResolver
{
    /**
     * @var ProductInformationFactory
     */
    private $productInformationFactory;

    /**
     * @var OrderItemRepositoryInterface
     */
    private $orderItemRepository;

    public function __construct(
        ProductInformationFactory $productInformationFactory,
        OrderItemRepositoryInterface $orderItemRepository
    ) {
        $this->productInformationFactory = $productInformationFactory;
        $this->orderItemRepository = $orderItemRepository;
    }

    /**
     * @param OrderInterface|InvoiceInterface|CreditmemoInterface|ShipmentInterface $itemContainer
     *
     * @return array<array{'item':OrderItemInterface|InvoiceItemInterface|CreditmemoItemInterface|ShipmentItemInterface, 'product':ProductInformationInterface}>
     *
     * @throws \Exception
     */
    public function resolveProductVariants($itemContainer)
    {
        if ($itemContainer instanceof OrderInterface) {
            return $this->resolveProductVariantsForOrder($itemContainer);
        }
        if ($itemContainer instanceof InvoiceInterface) {
            return $this->resolveProductVariantsForInvoice($itemContainer);
        }
        if ($itemContainer instanceof CreditmemoInterface) {
            return $this->resolveProductVariantsForCreditmemo($itemContainer);
        }
        if ($itemContainer instanceof ShipmentInterface) {
            return $this->resolveProductVariantsForShipment($itemContainer);
        }
        throw new \Exception('Unsupported item container! Use OrderInterface, InvoiceInterface, CreditmemoInterface or ShipmentInterface.');
    }

    /**
     * @return array<array{'item':OrderItemInterface, 'product':ProductInformationInterface}>
     */
    private function resolveProductVariantsForOrder(OrderInterface $order): array
    {
        // magento getItems() may return instance of Collection or array
        $orderItems = self::ensureArray(OrderItemInterface::class, $order->getItems());

        return $this->resolveProductVariantsForOrderItems($orderItems);
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderItemInterface[] $orderItems
     *
     * @return array<array{'item':OrderItemInterface, 'product':ProductInformationInterface}>
     */
    private function resolveProductVariantsForOrderItems(array $orderItems): array
    {
        // Create a map of order items by item ID
        $allItems = array_reduce($orderItems, function (array $carry, OrderItemInterface $orderItem) {
            $carry[intval($orderItem->getItemId())] = $orderItem;

            return $carry;
        }, []);

        // Create a map of all products by item ID
        $allProducts = array_reduce($allItems, function (array $carry, OrderItemInterface $orderItem) {
            $carry[intval($orderItem->getItemId())] = $this->productInformationFactory->createFromOrderItem($orderItem);

            return $carry;
        }, []);

        // Filter out configurable items as map of order items by item ID
        $configurableItems = array_filter($allItems, function (OrderItemInterface $orderItem) use ($allProducts) {
            return $allProducts[intval($orderItem->getItemId())]->isConfigurable();
        });

        // Filter out variant items as map of order items by parent item ID
        $variantItems = array_filter($allItems, function (OrderItemInterface $orderItem) use ($configurableItems) {
            return !is_null($orderItem->getParentItemId())
                && array_key_exists(intval($orderItem->getParentItemId()), $configurableItems)
                && 0.0 === floatval($orderItem->getPriceInclTax());
        });

        // Create a map of variant products by parent item ID
        $variantProductsOfConfigurableItems = array_reduce($variantItems, function (array $carry, OrderItemInterface $orderItem) use ($allProducts) {
            $carry[intval($orderItem->getParentItemId())] = $allProducts[intval($orderItem->getItemId())];

            return $carry;
        }, []);

        // Filter out order items for variants because they do not contain pricing information
        $itemsWithoutVariants = array_filter($allItems, function (OrderItemInterface $orderItem) use ($variantItems) {
            return !array_key_exists(intval($orderItem->getItemId()), $variantItems);
        });

        // Resolve products for order items
        $resolutions = array_map(function (OrderItemInterface $orderItem) use ($allProducts, $variantProductsOfConfigurableItems) {
            $product = $allProducts[intval($orderItem->getItemId())];

            if ($product->isConfigurable() && array_key_exists(intval($orderItem->getItemId()), $variantProductsOfConfigurableItems)) {
                $product = $variantProductsOfConfigurableItems[intval($orderItem->getItemId())];
            }

            return [
                'item' => $orderItem,
                'product' => $product,
            ];
        }, $itemsWithoutVariants);

        return array_values($resolutions);
    }

    /**
     * @return array<array{'item':InvoiceItemInterface, 'product':ProductInformationInterface}>
     */
    private function resolveProductVariantsForInvoice(InvoiceInterface $invoice): array
    {
        // magento getItems() may return instance of Collection or array
        $invoiceItems = self::ensureArray(InvoiceItemInterface::class, $invoice->getItems());

        return $this->resolveProductVariantsForInvoiceItems($invoiceItems);
    }

    /**
     * @param \Magento\Sales\Api\Data\InvoiceItemInterface[] $invoiceItems
     *
     * @return array<array{'item':InvoiceItemInterface, 'product':ProductInformationInterface}>
     */
    private function resolveProductVariantsForInvoiceItems(array $invoiceItems): array
    {
        $map = array_map(function (InvoiceItemInterface $invoiceItem) {
            return [
                'invoiceItem' => $invoiceItem,
                'orderItem' => $this->orderItemRepository->get($invoiceItem->getOrderItemId()),
            ];
        }, $invoiceItems);

        $orderItems = array_map(function ($item) {
            return $item['orderItem'];
        }, $map);

        $invoiceItemsByOrderItemId = array_reduce($map, function (array $carry, $item) {
            $carry[intval($item['orderItem']->getItemId())] = $item['invoiceItem'];

            return $carry;
        }, []);

        $resolutions = $this->resolveProductVariantsForOrderItems($orderItems);

        $resolutions = array_map(function ($resolution) use ($invoiceItemsByOrderItemId) {
            return [
                'item' => $invoiceItemsByOrderItemId[$resolution['item']->getItemId()],
                'product' => $resolution['product'],
            ];
        }, $resolutions);

        return array_values($resolutions);
    }

    /**
     * @return array<array{'item':CreditmemoItemInterface, 'product':ProductInformationInterface}>
     */
    private function resolveProductVariantsForCreditmemo(CreditmemoInterface $creditmemo): array
    {
        // magento getItems() may return instance of Collection or array
        $creditmemoItems = self::ensureArray(CreditmemoItemInterface::class, $creditmemo->getItems());

        return $this->resolveProductVariantsForCreditmemoItems($creditmemoItems);
    }

    /**
     * @param \Magento\Sales\Api\Data\CreditmemoItemInterface[] $creditmemoItems
     *
     * @return array<array{'item':CreditmemoItemInterface, 'product':ProductInformationInterface}>
     */
    private function resolveProductVariantsForCreditmemoItems(array $creditmemoItems): array
    {
        $map = array_map(function (CreditmemoItemInterface $creditmemoItem) {
            return [
                'creditmemoItem' => $creditmemoItem,
                'orderItem' => $this->orderItemRepository->get($creditmemoItem->getOrderItemId()),
            ];
        }, $creditmemoItems);

        $orderItems = array_map(function ($item) {
            return $item['orderItem'];
        }, $map);

        $creditmemoItemsByOrderItemId = array_reduce($map, function (array $carry, $item) {
            $carry[intval($item['orderItem']->getItemId())] = $item['creditmemoItem'];

            return $carry;
        }, []);

        $resolutions = $this->resolveProductVariantsForOrderItems($orderItems);

        $resolutions = array_map(function ($resolution) use ($creditmemoItemsByOrderItemId) {
            return [
                'item' => $creditmemoItemsByOrderItemId[$resolution['item']->getItemId()],
                'product' => $resolution['product'],
            ];
        }, $resolutions);

        return array_values($resolutions);
    }

    /**
     * @return array<array{'item':ShipmentItemInterface, 'product':ProductInformationInterface}>
     */
    private function resolveProductVariantsForShipment(ShipmentInterface $shipment): array
    {
        // magento getItems() may return instance of Collection or array
        $shipmentItems = self::ensureArray(ShipmentItemInterface::class, $shipment->getItems());

        return $this->resolveProductVariantsForShipmentItems($shipmentItems);
    }

    /**
     * @param \Magento\Sales\Api\Data\ShipmentItemInterface[] $shipmentItems
     *
     * @return array<array{'item':ShipmentItemInterface, 'product':ProductInformationInterface}>
     */
    private function resolveProductVariantsForShipmentItems(array $shipmentItems): array
    {
        $map = array_map(function (ShipmentItemInterface $shipmentItem) {
            return [
                'shipmentItem' => $shipmentItem,
                'orderItem' => $this->orderItemRepository->get($shipmentItem->getOrderItemId()),
            ];
        }, $shipmentItems);

        $orderItems = array_map(function ($item) {
            return $item['orderItem'];
        }, $map);

        $shipmentItemsByOrderItemId = array_reduce($map, function (array $carry, $item) {
            $carry[intval($item['orderItem']->getItemId())] = $item['shipmentItem'];

            return $carry;
        }, []);

        $resolutions = $this->resolveProductVariantsForOrderItems($orderItems);

        $resolutions = array_map(function ($resolution) use ($shipmentItemsByOrderItemId) {
            return [
                'item' => $shipmentItemsByOrderItemId[$resolution['item']->getItemId()],
                'product' => $resolution['product'],
            ];
        }, $resolutions);

        return array_values($resolutions);
    }

    /**
     * @phpstan-template T
     *
     * @param class-string<T> $itemClass
     * @param iterable<T>     $items
     *
     * @return array<T>
     */
    private static function ensureArray($itemClass, $items): array
    {
        if ($items instanceof \Magento\Framework\Data\Collection) {
            /** @var T[] */
            $items = $items->getItems();
        }

        if (!is_array($items)) {
            $array = [];
            foreach ($items as $item) {
                array_push($array, $item);
            }
            $items = $array;
        }

        return $items;
    }
}
