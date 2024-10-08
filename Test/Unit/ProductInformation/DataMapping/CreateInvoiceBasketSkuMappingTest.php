<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\Tests\Unit\ProductInformation\DataMapping;

use Axytos\KaufAufRechnung\DataMapping\CreateInvoiceBasketDtoFactory;
use Axytos\KaufAufRechnung\DataMapping\CreateInvoiceBasketPositionDtoCollectionFactory;
use Axytos\KaufAufRechnung\DataMapping\CreateInvoiceBasketPositionDtoFactory;
use Axytos\KaufAufRechnung\DataMapping\CreateInvoiceTaxGroupDtoCollectionFactory;
use Axytos\KaufAufRechnung\DataMapping\CreateInvoiceTaxGroupDtoFactory;
use Axytos\KaufAufRechnung\ProductInformation\ProductInformationFactory;
use Axytos\KaufAufRechnung\ProductInformation\ProductTypeCodes;
use Axytos\KaufAufRechnung\ProductInformation\ProductVariantResolver;
use Axytos\KaufAufRechnung\Test\Unit\ProductInformation\ProductInformationAssertionTrait;
use Axytos\KaufAufRechnung\Test\Unit\ProductInformation\ProductInformationMockFactoryTrait;
use Axytos\KaufAufRechnung\ValueCalculation\ShippingPositionTaxPercentCalculator;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Pricing\Price\ConfigurableOptionsProviderInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class CreateInvoiceBasketSkuMappingTest extends TestCase
{
    use ProductInformationMockFactoryTrait;
    use ProductInformationAssertionTrait;

    /**
     * @var CreateInvoiceBasketDtoFactory
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
        $productVariantResolver = new ProductVariantResolver($productInformationFactory, $this->orderItemRepository);
        $this->sut = new CreateInvoiceBasketDtoFactory(
            new CreateInvoiceBasketPositionDtoCollectionFactory(
                new CreateInvoiceBasketPositionDtoFactory(
                    $this->orderItemRepository,
                    new ShippingPositionTaxPercentCalculator()
                ),
                $productVariantResolver
            ),
            new CreateInvoiceTaxGroupDtoCollectionFactory(
                new CreateInvoiceTaxGroupDtoFactory(
                    $this->orderItemRepository,
                    new ShippingPositionTaxPercentCalculator()
                ),
                $productVariantResolver
            )
        );
    }

    /**
     * @return void
     */
    public function test_sku_mapping_for_not_configurable_items()
    {
        $this->setUpProductRepository($this->productRepository, [
            ['getId' => 1, 'getSku' => 'sku1', 'getTypeId' => ProductTypeCodes::SIMPLE],
            ['getId' => 2, 'getSku' => 'sku2', 'getTypeId' => ProductTypeCodes::BUNDLE],
            ['getId' => 3, 'getSku' => 'sku3', 'getTypeId' => ProductTypeCodes::GROUPED],
            ['getId' => 4, 'getSku' => 'sku4', 'getTypeId' => ProductTypeCodes::VIRTUAL],
            ['getId' => 5, 'getSku' => 'sku5', 'getTypeId' => ProductTypeCodes::DOWNLOADABLE],
        ]);

        $order = $this->createOrderMock([
            ['getItemId' => 1, 'getParentItemId' => null, 'getProductId' => 1],
            ['getItemId' => 2, 'getParentItemId' => null, 'getProductId' => 2],
            ['getItemId' => 3, 'getParentItemId' => null, 'getProductId' => 3],
            ['getItemId' => 4, 'getParentItemId' => null, 'getProductId' => 4],
            ['getItemId' => 5, 'getParentItemId' => null, 'getProductId' => 5],
        ]);

        $this->setUpOrderItemRepository($this->orderItemRepository, [$order]);

        $invoice = $this->createInvoiceMock([
            ['getItemId' => 1, 'getOrderItemId' => 1],
            ['getItemId' => 2, 'getOrderItemId' => 2],
            ['getItemId' => 3, 'getOrderItemId' => 3],
            ['getItemId' => 4, 'getOrderItemId' => 4],
            ['getItemId' => 5, 'getOrderItemId' => 5],
        ]);

        $basket = $this->sut->create($invoice);

        // assert 5 position + 1 for shipping
        $this->assertCount(6, $basket->positions);

        $positions = $basket->positions;
        $this->assertEquals('sku1', $positions[0]->productId);
        $this->assertEquals('sku2', $positions[1]->productId);
        $this->assertEquals('sku3', $positions[2]->productId);
        $this->assertEquals('sku4', $positions[3]->productId);
        $this->assertEquals('sku5', $positions[4]->productId);
        $this->assertEquals('0', $positions[5]->productId);
    }

    /**
     * @return void
     */
    public function test_sku_mapping_for_configurable_items()
    {
        $this->setUpProductRepository($this->productRepository, [
            ['getId' => 1, 'getSku' => 'sku1', 'getTypeId' => ProductTypeCodes::SIMPLE],
            ['getId' => 2, 'getSku' => 'sku2', 'getTypeId' => ProductTypeCodes::BUNDLE],
            ['getId' => 3, 'getSku' => 'sku3', 'getTypeId' => ProductTypeCodes::GROUPED],
            ['getId' => 4, 'getSku' => 'sku4', 'getTypeId' => ProductTypeCodes::VIRTUAL],
            ['getId' => 5, 'getSku' => 'sku5', 'getTypeId' => ProductTypeCodes::DOWNLOADABLE],
            ['getId' => 6, 'getSku' => 'sku6', 'getTypeId' => ProductTypeCodes::CONFIGURABLE],
            ['getId' => 7, 'getSku' => 'sku7', 'getTypeId' => ProductTypeCodes::CONFIGURABLE],
            ['getId' => 8, 'getSku' => 'sku7-blu', 'getTypeId' => ProductTypeCodes::SIMPLE],
            ['getId' => 9, 'getSku' => 'sku6-red', 'getTypeId' => ProductTypeCodes::SIMPLE],
        ]);

        $order = $this->createOrderMock([
            ['getItemId' => 1, 'getParentItemId' => null, 'getProductId' => 1],
            ['getItemId' => 2, 'getParentItemId' => null, 'getProductId' => 2],
            ['getItemId' => 3, 'getParentItemId' => null, 'getProductId' => 3],
            ['getItemId' => 4, 'getParentItemId' => null, 'getProductId' => 4],
            ['getItemId' => 5, 'getParentItemId' => null, 'getProductId' => 5],
            ['getItemId' => 6, 'getParentItemId' => null, 'getProductId' => 6],
            ['getItemId' => 7, 'getParentItemId' => null, 'getProductId' => 7],
            ['getItemId' => 8, 'getParentItemId' => 7, 'getProductId' => 8],
            ['getItemId' => 9, 'getParentItemId' => 6, 'getProductId' => 9],
        ]);

        $this->setUpOrderItemRepository($this->orderItemRepository, [$order]);

        $invoice = $this->createInvoiceMock([
            ['getItemId' => 1, 'getOrderItemId' => 1],
            ['getItemId' => 2, 'getOrderItemId' => 2],
            ['getItemId' => 3, 'getOrderItemId' => 3],
            ['getItemId' => 4, 'getOrderItemId' => 4],
            ['getItemId' => 5, 'getOrderItemId' => 5],
            ['getItemId' => 6, 'getOrderItemId' => 6],
            ['getItemId' => 7, 'getOrderItemId' => 7],
            ['getItemId' => 8, 'getOrderItemId' => 8],
            ['getItemId' => 9, 'getOrderItemId' => 9],
        ]);

        $basket = $this->sut->create($invoice);

        // assert 7 position + 1 for shipping
        $this->assertCount(8, $basket->positions);

        $positions = $basket->positions;
        $this->assertEquals('sku1', $positions[0]->productId);
        $this->assertEquals('sku2', $positions[1]->productId);
        $this->assertEquals('sku3', $positions[2]->productId);
        $this->assertEquals('sku4', $positions[3]->productId);
        $this->assertEquals('sku5', $positions[4]->productId);
        $this->assertEquals('sku6-red', $positions[5]->productId);
        $this->assertEquals('sku7-blu', $positions[6]->productId);
        $this->assertEquals('0', $positions[7]->productId);
    }
}
