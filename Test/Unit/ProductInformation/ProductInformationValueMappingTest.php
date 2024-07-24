<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\Tests\Unit\ProductInformation;

use Axytos\KaufAufRechnung\ProductInformation\ProductTypeCodes;
use Axytos\KaufAufRechnung\Test\Unit\ProductInformation\ProductInformationAssertionTrait;
use Axytos\KaufAufRechnung\Test\Unit\ProductInformation\ProductInformationMockFactoryTrait;
use PHPUnit\Framework\Attributes\DataProvider;

class ProductInformationValueMappingTest extends ProductInformationTestCase
{
    use ProductInformationMockFactoryTrait;
    use ProductInformationAssertionTrait;

    /**
     * @dataProvider getProductTypeCodes
     * @param string $productTypeCode
     * @return void
     */
    #[DataProvider('getProductTypeCodes')]
    public function test($productTypeCode)
    {
        $this->setUpCategoryRepository($this->categoryRepository, [
            ['getId' => 1, 'getName' => 'Category1'],
            ['getId' => 2, 'getName' => 'Category2'],
            ['getId' => 3, 'getName' => 'Category3'],
        ]);

        $this->setUpProductRepository($this->productRepository, [
            [
                'getId' => 1,
                'getSku' => 'sku1',
                'getName' => 'Product1',
                'custom_attributes' => [
                    'category_ids' => [2,3]
                ],
                'getTypeId' => $productTypeCode
            ],
            [
                'getId' => 2,
                'getSku' => 'sku2',
                'getName' => 'Product2',
                'custom_attributes' => [
                    'category_ids' => [1,3]
                ],
                'getTypeId' => $productTypeCode
            ],
        ]);

        $order = $this->createOrderMock([
            [
                'getItemId' => 1,
                'getParentItemId' => null,
                'getProductId' => 1,
            ],
            [
                'getItemId' => 2,
                'getParentItemId' => null,
                'getProductId' => 2,
            ],
        ]);

        $resolution = $this->sut->resolveProductVariants($order);

        $actual = $resolution[0]['product'];

        $this->assertEquals('sku1', $actual->getSku());
        $this->assertEquals('Product1', $actual->getName());
        $this->assertEquals('Category2;Category3', $actual->getCategory());

        if ($productTypeCode === ProductTypeCodes::CONFIGURABLE) {
            $this->assertEquals(true, $actual->isConfigurable());
        } else {
            $this->assertEquals(false, $actual->isConfigurable());
        }

        $actual = $resolution[1]['product'];

        $this->assertEquals('sku2', $actual->getSku());
        $this->assertEquals('Product2', $actual->getName());
        $this->assertEquals('Category1;Category3', $actual->getCategory());

        if ($productTypeCode === ProductTypeCodes::CONFIGURABLE) {
            $this->assertEquals(true, $actual->isConfigurable());
        } else {
            $this->assertEquals(false, $actual->isConfigurable());
        }
    }

    /**
     * @return array<array{string}>
     */
    public static function getProductTypeCodes(): array
    {
        return [
            [ProductTypeCodes::SIMPLE],
            [ProductTypeCodes::CONFIGURABLE],
            [ProductTypeCodes::BUNDLE],
            [ProductTypeCodes::GROUPED],
            [ProductTypeCodes::VIRTUAL],
            [ProductTypeCodes::DOWNLOADABLE],
        ];
    }
}
