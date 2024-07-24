<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\ProductInformation;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Pricing\Price\ConfigurableOptionsProviderInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;

class ProductInformationFactory
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    private $categoryRepository;
    /**
     * @var \Magento\ConfigurableProduct\Pricing\Price\ConfigurableOptionsProviderInterface
     */
    private $configurableOptionsProvider;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository,
        ConfigurableOptionsProviderInterface $configurableOptionsProvider
    ) {
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->configurableOptionsProvider = $configurableOptionsProvider;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return null|\Axytos\KaufAufRechnung\ProductInformation\ProductInformationInterface
     */
    public function createFromProduct(ProductInterface $product): ?ProductInformationInterface
    {
        return new ProductInformation(
            $product,
            $this->loadProductCategoryNames($product),
            $this->loadVariants($product)
        );
    }

    /**
     * @param string|int|null $productId
     * @return null|\Axytos\KaufAufRechnung\ProductInformation\ProductInformationInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createFromProductId($productId): ?ProductInformationInterface
    {
        if (is_null($productId)) {
            return null;
        }
        /** @var ProductInterface */
        $product = $this->productRepository->getById(intval($productId));
        return $this->createFromProduct($product);
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderItemInterface $orderItem
     * @return null|\Axytos\KaufAufRechnung\ProductInformation\ProductInformationInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createFromOrderItem(OrderItemInterface $orderItem): ?ProductInformationInterface
    {
        return $this->createFromProductId($orderItem->getProductId());
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return \Axytos\KaufAufRechnung\ProductInformation\ProductInformationInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createFromOrder(OrderInterface $order): array
    {
        $productInformations = [];
        foreach ($order->getItems() as $orderItem) {
            $productInformation = $this->createFromOrderItem($orderItem);
            if (!is_null($productInformation)) {
                array_push($productInformations, $productInformation);
            }
        }
        return $productInformations;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return string[]
     */
    private function loadProductCategoryNames(ProductInterface $product): array
    {
        $categories = $this->loadProductCategories($product);
        $categoryNames = [];
        foreach ($categories as $category) {
            $name = $category->getName();
            if (!is_null($name)) {
                array_push($categoryNames, $name);
            }
        }
        return $categoryNames;
    }

    /**
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return \Magento\Catalog\Api\Data\CategoryInterface[]
     */
    private function loadProductCategories(ProductInterface $product): array
    {
        /** @var array<mixed> */
        $categoryIds = $this->loadProductCategoryIds($product);

        $categories = [];
        foreach ($categoryIds as $categoryId) {
            try {
                /** @var \Magento\Catalog\Api\Data\CategoryInterface */
                $category = $this->categoryRepository->get(intval($categoryId));
                array_push($categories, $category);
            } catch (NoSuchEntityException $th) {
                // skip
            }
        }
        return $categories;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return array<mixed>
     */
    private function loadProductCategoryIds(ProductInterface $product): array
    {
        /** @var \Magento\Framework\Api\AttributeInterface|null */
        $categoryIdsAttribute = $product->getCustomAttribute('category_ids');

        if (is_null($categoryIdsAttribute)) {
            return [];
        }

        /** @var array<mixed> */
        $categoryIds = $categoryIdsAttribute->getValue();
        if (!is_array($categoryIds)) {
            return [];
        }

        return $categoryIds;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return \Magento\Catalog\Api\Data\ProductInterface[]
     */
    private function loadVariants(ProductInterface $product): array
    {
        $variants = $this->configurableOptionsProvider->getProducts($product);
        if (is_null($variants)) {
            return [];
        }
        return $variants;
    }
}
