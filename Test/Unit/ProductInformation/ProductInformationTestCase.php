<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\Tests\Unit\ProductInformation;

use Axytos\KaufAufRechnung\ProductInformation\ProductInformationFactory;
use Axytos\KaufAufRechnung\ProductInformation\ProductVariantResolver;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Pricing\Price\ConfigurableOptionsProviderInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class ProductInformationTestCase extends TestCase
{
    /**
     * @var ProductVariantResolver
     */
    protected $sut;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    protected $productRepository;
    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    protected $categoryRepository;
    /**
     * @var \Magento\Sales\Api\OrderItemRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    protected $orderItemRepository;
    /**
     * @var \Magento\ConfigurableProduct\Pricing\Price\ConfigurableOptionsProviderInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    protected $configurableOptionsProvider;

    public function setUp(): void
    {
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->categoryRepository = $this->createMock(CategoryRepositoryInterface::class);
        $this->orderItemRepository = $this->createMock(OrderItemRepositoryInterface::class);
        $this->configurableOptionsProvider = $this->createMock(ConfigurableOptionsProviderInterface::class);
        $productInformationFactory = new ProductInformationFactory(
            $this->productRepository,
            $this->categoryRepository,
            $this->configurableOptionsProvider
        );
        $this->sut = new ProductVariantResolver($productInformationFactory, $this->orderItemRepository);
    }
}
