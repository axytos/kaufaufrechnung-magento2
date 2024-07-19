<?php

namespace Axytos\KaufAufRechnung\Test\Unit\ProductInformation;

use Axytos\KaufAufRechnung\ProductInformation\ProductInformationInterface;
use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\ShipmentItemInterface;

trait ProductInformationAssertionTrait
{
    /**
     * @param array<array{'item':OrderItemInterface|InvoiceItemInterface|CreditmemoItemInterface|ShipmentItemInterface, 'product':ProductInformationInterface}> $resolutions
     * @return void
     */
    protected function assertUniqueProductSKUs(array $resolutions): void
    {
        $productInformations = array_map(function (array $resolution) {
            return $resolution['product'];
        }, $resolutions);
        $skus = array_map(function (ProductInformationInterface $product) {
            return $product->getSku();
        }, $productInformations);
        $uniqueSkus = array_unique($skus);
        $this->assertCount(count($skus), $uniqueSkus, "All SKUs should be unique, found: " . join(', ', $skus));
    }
}
