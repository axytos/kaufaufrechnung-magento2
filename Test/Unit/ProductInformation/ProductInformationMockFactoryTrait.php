<?php

namespace Axytos\KaufAufRechnung\Test\Unit\ProductInformation;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductSearchResultsInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\Data\ShipmentItemInterface;

trait ProductInformationMockFactoryTrait
{
    // ====================================================================================================

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject $productRepository
     * @param array<array<string,mixed>>                                                               $productSetup
     *
     * @return void
     */
    private function setUpProductRepository($productRepository, $productSetup)
    {
        $products = $this->createManyProductMocks($productSetup);
        $productsTable = array_reduce($products, function (array $carry, ProductInterface $product) {
            $carry[intval($product->getId())] = $product;

            return $carry;
        }, []);
        $productRepository->method('getById')->willReturnCallback(function ($productId) use ($productsTable) {
            if (!array_key_exists($productId, $productsTable)) {
                throw new \Magento\Framework\Exception\NoSuchEntityException();
            }

            return $productsTable[$productId];
        });
        $productRepository->method('getList')->willReturnCallback(function () use ($products) {
            $searchResult = $this->createMock(ProductSearchResultsInterface::class);
            $searchResult->method('getItems')->willReturn($products);

            return $searchResult;
        });
    }

    // ====================================================================================================

    /**
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject $categoryRepository
     * @param array<array<string,mixed>>                                                                $categorySetup
     *
     * @return void
     */
    private function setUpCategoryRepository($categoryRepository, $categorySetup)
    {
        $categories = $this->createManyCategoryMocks($categorySetup);
        $categoriesTable = array_reduce($categories, function (array $carry, CategoryInterface $category) {
            $carry[intval($category->getId())] = $category;

            return $carry;
        }, []);
        $categoryRepository->method('get')->willReturnCallback(function ($categoryId) use ($categoriesTable) {
            if (!array_key_exists($categoryId, $categoriesTable)) {
                throw new \Magento\Framework\Exception\NoSuchEntityException();
            }

            return $categoriesTable[$categoryId];
        });
    }

    // ====================================================================================================

    /**
     * @param \Magento\Sales\Api\OrderItemRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject $orderItemRepository
     * @param OrderInterface[]                                                                         $orders
     *
     * @return void
     */
    private function setUpOrderItemRepository($orderItemRepository, $orders)
    {
        $orderItems = array_reduce($orders, function (array $carry, OrderInterface $order) {
            return array_merge($carry, $order->getItems());
        }, []);
        $orderItemsTable = array_reduce($orderItems, function (array $carry, OrderItemInterface $orderItem) {
            $carry[intval($orderItem->getItemId())] = $orderItem;

            return $carry;
        }, []);
        $orderItemRepository->method('get')->willReturnCallback(function ($orderItemId) use ($orderItemsTable) {
            if (!array_key_exists($orderItemId, $orderItemsTable)) {
                throw new \Magento\Framework\Exception\NoSuchEntityException();
            }

            return $orderItemsTable[$orderItemId];
        });
    }

    // ====================================================================================================

    /**
     * @param array<string,mixed> $methodSetup
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createProductMock(array $methodSetup)
    {
        return $this->createDataMock(ProductInterface::class, $methodSetup);
    }

    /**
     * @param array<array<string,mixed>> $manyMethodSetups
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface[]&\PHPUnit\Framework\MockObject\MockObject[]
     */
    protected function createManyProductMocks(array $manyMethodSetups)
    {
        return $this->createManyDataMocks(ProductInterface::class, $manyMethodSetups);
    }

    // ====================================================================================================

    /**
     * @param array<string,mixed> $methodSetup
     *
     * @return \Magento\Catalog\Api\Data\CategoryInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createCategoryMock(array $methodSetup)
    {
        return $this->createDataMock(CategoryInterface::class, $methodSetup);
    }

    /**
     * @param array<array<string,mixed>> $manyMethodSetups
     *
     * @return \Magento\Catalog\Api\Data\CategoryInterface[]&\PHPUnit\Framework\MockObject\MockObject[]
     */
    protected function createManyCategoryMocks(array $manyMethodSetups)
    {
        return $this->createManyDataMocks(CategoryInterface::class, $manyMethodSetups);
    }

    // ====================================================================================================

    /**
     * @param array<array<string,mixed>> $orderItemsSetup
     *
     * @return \Magento\Sales\Api\Data\OrderInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createOrderMock(array $orderItemsSetup)
    {
        $orderItems = $this->createManyOrderItemMocks($orderItemsSetup);

        return $this->createDataMock(OrderInterface::class, ['getItems' => $orderItems]);
    }

    /**
     * @param array<string,mixed> $methodSetup
     *
     * @return \Magento\Sales\Api\Data\OrderItemInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createOrderItemMock(array $methodSetup)
    {
        return $this->createDataMock(OrderItemInterface::class, $methodSetup);
    }

    /**
     * @param array<array<string,mixed>> $manyMethodSetups
     *
     * @return \Magento\Sales\Api\Data\OrderItemInterface[]&\PHPUnit\Framework\MockObject\MockObject[]
     */
    protected function createManyOrderItemMocks(array $manyMethodSetups)
    {
        return $this->createManyDataMocks(OrderItemInterface::class, $manyMethodSetups);
    }

    // ====================================================================================================

    /**
     * @param array<array<string,mixed>> $invoiceItemsSetup
     *
     * @return \Magento\Sales\Api\Data\InvoiceInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createInvoiceMock(array $invoiceItemsSetup)
    {
        $invoiceItems = $this->createManyInvoiceItemMocks($invoiceItemsSetup);

        return $this->createDataMock(InvoiceInterface::class, ['getItems' => $invoiceItems]);
    }

    /**
     * @param array<string,mixed> $methodSetup
     *
     * @return \Magento\Sales\Api\Data\InvoiceItemInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createInvoiceItemMock(array $methodSetup)
    {
        return $this->createDataMock(InvoiceItemInterface::class, $methodSetup);
    }

    /**
     * @param array<array<string,mixed>> $manyMethodSetups
     *
     * @return \Magento\Sales\Api\Data\InvoiceItemInterface[]&\PHPUnit\Framework\MockObject\MockObject[]
     */
    protected function createManyInvoiceItemMocks(array $manyMethodSetups)
    {
        return $this->createManyDataMocks(InvoiceItemInterface::class, $manyMethodSetups);
    }

    // ====================================================================================================

    /**
     * @param array<array<string,mixed>> $creditmemoItemsSetup
     *
     * @return \Magento\Sales\Api\Data\CreditmemoInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createCreditmemoMock(array $creditmemoItemsSetup)
    {
        $creditmemoItems = $this->createManyCreditmemoItemMocks($creditmemoItemsSetup);

        return $this->createDataMock(CreditmemoInterface::class, ['getItems' => $creditmemoItems]);
    }

    /**
     * @param array<string,mixed> $methodSetup
     *
     * @return \Magento\Sales\Api\Data\CreditmemoItemInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createCreditmemoItemMock(array $methodSetup)
    {
        return $this->createDataMock(CreditmemoItemInterface::class, $methodSetup);
    }

    /**
     * @param array<array<string,mixed>> $manyMethodSetups
     *
     * @return \Magento\Sales\Api\Data\CreditmemoItemInterface[]&\PHPUnit\Framework\MockObject\MockObject[]
     */
    protected function createManyCreditmemoItemMocks(array $manyMethodSetups)
    {
        return $this->createManyDataMocks(CreditmemoItemInterface::class, $manyMethodSetups);
    }

    // ====================================================================================================

    /**
     * @param array<array<string,mixed>> $shipmentItemsSetup
     *
     * @return \Magento\Sales\Api\Data\ShipmentInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createShipmentMock(array $shipmentItemsSetup)
    {
        $shipmentItems = $this->createManyShipmentItemMocks($shipmentItemsSetup);

        return $this->createDataMock(ShipmentInterface::class, ['getItems' => $shipmentItems]);
    }

    /**
     * @param array<string,mixed> $methodSetup
     *
     * @return \Magento\Sales\Api\Data\ShipmentItemInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createShipmentItemMock(array $methodSetup)
    {
        return $this->createDataMock(ShipmentItemInterface::class, $methodSetup);
    }

    /**
     * @param array<array<string,mixed>> $manyMethodSetups
     *
     * @return \Magento\Sales\Api\Data\ShipmentItemInterface[]&\PHPUnit\Framework\MockObject\MockObject[]
     */
    protected function createManyShipmentItemMocks(array $manyMethodSetups)
    {
        return $this->createManyDataMocks(ShipmentItemInterface::class, $manyMethodSetups);
    }

    // ====================================================================================================

    /**
     * @phpstan-template T
     *
     * @param class-string<T>     $class
     * @param array<string,mixed> $methodSetup
     *
     * @return T&\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createDataMock(string $class, array $methodSetup)
    {
        $object = $this->createMock($class);
        foreach ($methodSetup as $key => $value) {
            if (
                \Magento\Framework\Api\CustomAttributesDataInterface::CUSTOM_ATTRIBUTES === $key
                && is_subclass_of($class, \Magento\Framework\Api\CustomAttributesDataInterface::class)
            ) {
                $object->method('getCustomAttribute')->willReturnCallback(function ($attributeCode) use ($value) {
                    if (!is_array($value)) {
                        return null;
                    }
                    if (!array_key_exists($attributeCode, $value)) {
                        return null;
                    }
                    $attribute = $this->createMock(\Magento\Framework\Api\AttributeInterface::class);
                    $attribute->method('getValue')->willReturn($value[$attributeCode]);

                    return $attribute;
                });
            } elseif (method_exists($class, $key)) {
                $object->method($key)->willReturn($value);
            }
        }

        return $object;
    }

    /**
     * @phpstan-template T
     *
     * @param class-string<T>            $class
     * @param array<array<string,mixed>> $manyMethodSetups
     *
     * @return T[]&\PHPUnit\Framework\MockObject\MockObject[]
     */
    protected function createManyDataMocks(string $class, array $manyMethodSetups)
    {
        return array_map(function ($methodSetup) use ($class) {
            return $this->createDataMock($class, $methodSetup);
        }, $manyMethodSetups);
    }
}
