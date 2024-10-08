<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\DataMapping;

use Axytos\ECommerce\DataTransferObjects\ShippingBasketPositionDtoCollection;
use Axytos\KaufAufRechnung\ProductInformation\ProductVariantResolver;
use Magento\Sales\Api\Data\ShipmentInterface;

class ShippingBasketPositionDtoCollectionFactory
{
    /**
     * @var ShippingBasketPositionDtoFactory
     */
    private $shippingBasketPositionDtoFactory;

    /**
     * @var ProductVariantResolver
     */
    private $productVariantResolver;

    public function __construct(
        ShippingBasketPositionDtoFactory $shippingBasketPositionDtoFactory,
        ProductVariantResolver $productVariantResolver
    ) {
        $this->shippingBasketPositionDtoFactory = $shippingBasketPositionDtoFactory;
        $this->productVariantResolver = $productVariantResolver;
    }

    public function create(ShipmentInterface $shipment): ShippingBasketPositionDtoCollection
    {
        $productVariantResolution = $this->productVariantResolver->resolveProductVariants($shipment);

        $positions = array_map(function ($itemResolution) {
            /** @var \Magento\Sales\Api\Data\ShipmentItemInterface $shipmentItem */
            $shipmentItem = $itemResolution['item'];
            /** @var \Axytos\KaufAufRechnung\ProductInformation\ProductInformationInterface $productInfo */
            $productInfo = $itemResolution['product'];

            return $this->shippingBasketPositionDtoFactory->create($shipmentItem, $productInfo);
        }, $productVariantResolution);

        $positions = array_values($positions);

        $shippingPosition = $this->shippingBasketPositionDtoFactory->createShippingPosition();
        array_push($positions, $shippingPosition);

        return new ShippingBasketPositionDtoCollection(...$positions);
    }
}
