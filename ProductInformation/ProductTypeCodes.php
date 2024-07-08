<?php

namespace Axytos\KaufAufRechnung\ProductInformation;

/**
 * see: https://docs.magento.com/user-guide/v2.3/catalog/product-types.html
 *
 * @package Axytos\KaufAufRechnung\ProductInformation
 */
class ProductTypeCodes
{
    const SIMPLE = \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE; // == 'simple'
    const BUNDLE = \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE; // == 'bundle';
    const VIRTUAL = \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE; // == 'virtual';
    const CONFIGURABLE = \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE; // == 'configurable'
    const GROUPED = \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE; // == 'grouped';
    const DOWNLOADABLE = \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE; // == 'downloadable';
}
