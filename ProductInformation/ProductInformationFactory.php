<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\ProductInformation;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Pricing\Price\ConfigurableOptionsProviderInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;

class ProductInformationFactory
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;
    /**
     * @var ConfigurableOptionsProviderInterface
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
     *
     * @throws NoSuchEntityException
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
     * @throws NoSuchEntityException
     */
    public function createFromOrderItem(OrderItemInterface $orderItem): ?ProductInformationInterface
    {
        return $this->createFromProductId($orderItem->getProductId());
    }

    /**
     * @return \Axytos\KaufAufRechnung\ProductInformation\ProductInformationInterface[]
     *
     * @throws NoSuchEntityException
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
