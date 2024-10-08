<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\Tests\Unit\ProductInformation;

use Axytos\KaufAufRechnung\ProductInformation\ProductTypeCodes;
use Axytos\KaufAufRechnung\Test\Unit\ProductInformation\ProductInformationAssertionTrait;
use Axytos\KaufAufRechnung\Test\Unit\ProductInformation\ProductInformationMockFactoryTrait;
use Magento\Framework\Api\SearchCriteria;

/**
 * @internal
 */
class ResolveCreditmemoItemsTest extends ProductInformationTestCase
{
    use ProductInformationMockFactoryTrait;
    use ProductInformationAssertionTrait;

    /**
     * @return void
     */
    public function test_not_configurable_creditmemo_items_are_mapped_to_their_products()
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

        $creditmemo = $this->createCreditmemoMock([
            ['getItemId' => 1, 'getOrderItemId' => 1],
            ['getItemId' => 2, 'getOrderItemId' => 2],
            ['getItemId' => 3, 'getOrderItemId' => 3],
            ['getItemId' => 4, 'getOrderItemId' => 4],
            ['getItemId' => 5, 'getOrderItemId' => 5],
        ]);

        $resolution = $this->sut->resolveProductVariants($creditmemo);

        $this->assertCount(5, $resolution);

        $creditmemoItems = $creditmemo->getItems();
        $this->assertSame($creditmemoItems[0], $resolution[0]['item'], 'Creditmemo item 0 should be the same as the first item in the resolution');
        $this->assertSame($creditmemoItems[1], $resolution[1]['item'], 'Creditmemo item 1 should be the same as the first item in the resolution');
        $this->assertSame($creditmemoItems[2], $resolution[2]['item'], 'Creditmemo item 2 should be the same as the first item in the resolution');
        $this->assertSame($creditmemoItems[3], $resolution[3]['item'], 'Creditmemo item 3 should be the same as the first item in the resolution');
        $this->assertSame($creditmemoItems[4], $resolution[4]['item'], 'Creditmemo item 4 should be the same as the first item in the resolution');

        $products = $this->productRepository->getList(new SearchCriteria())->getItems();
        $this->assertEquals($products[0]->getId(), $resolution[0]['product']->getId());
        $this->assertEquals($products[1]->getId(), $resolution[1]['product']->getId());
        $this->assertEquals($products[2]->getId(), $resolution[2]['product']->getId());
        $this->assertEquals($products[3]->getId(), $resolution[3]['product']->getId());
        $this->assertEquals($products[4]->getId(), $resolution[4]['product']->getId());

        $this->assertUniqueProductSKUs($resolution);
    }

    /**
     * @return void
     */
    public function test_configurable_creditmemo_items_are_mapped_to_their_products()
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

        $creditmemo = $this->createCreditmemoMock([
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

        $resolution = $this->sut->resolveProductVariants($creditmemo);

        $this->assertCount(7, $resolution);

        $creditmemoItems = $creditmemo->getItems();
        $this->assertSame($creditmemoItems[0], $resolution[0]['item'], 'Creditmemo item 0 should be the same as the first item in the resolution');
        $this->assertSame($creditmemoItems[1], $resolution[1]['item'], 'Creditmemo item 1 should be the same as the first item in the resolution');
        $this->assertSame($creditmemoItems[2], $resolution[2]['item'], 'Creditmemo item 2 should be the same as the first item in the resolution');
        $this->assertSame($creditmemoItems[3], $resolution[3]['item'], 'Creditmemo item 3 should be the same as the first item in the resolution');
        $this->assertSame($creditmemoItems[4], $resolution[4]['item'], 'Creditmemo item 4 should be the same as the first item in the resolution');
        $this->assertSame($creditmemoItems[5], $resolution[5]['item'], 'Creditmemo item 5 should be the same as the first item in the resolution');
        $this->assertSame($creditmemoItems[6], $resolution[6]['item'], 'Creditmemo item 6 should be the same as the first item in the resolution');

        $products = $this->productRepository->getList(new SearchCriteria())->getItems();
        // without variants
        $this->assertEquals($products[0]->getId(), $resolution[0]['product']->getId());
        $this->assertEquals($products[1]->getId(), $resolution[1]['product']->getId());
        $this->assertEquals($products[2]->getId(), $resolution[2]['product']->getId());
        $this->assertEquals($products[3]->getId(), $resolution[3]['product']->getId());
        $this->assertEquals($products[4]->getId(), $resolution[4]['product']->getId());

        // with variants
        // red variant of sku6
        $this->assertEquals(9, $resolution[5]['product']->getId());
        $this->assertEquals('sku6-red', $resolution[5]['product']->getSku());
        $this->assertEquals($products[8]->getId(), $resolution[5]['product']->getId());
        // blu variant of sku7
        $this->assertEquals(8, $resolution[6]['product']->getId());
        $this->assertEquals('sku7-blu', $resolution[6]['product']->getSku());
        $this->assertEquals($products[7]->getId(), $resolution[6]['product']->getId());

        $this->assertUniqueProductSKUs($resolution);

        $resolutionSKUs = array_map(function ($item) {
            return $item['product']->getSku();
        }, $resolution);
        $this->assertNotContainsEquals('sku6', $resolutionSKUs, 'sku6 of configurable product should not be in the resolution');
        $this->assertNotContainsEquals('sku7', $resolutionSKUs, 'sku7 of configurable product should not be in the resolution');
    }
}
