<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\DataMapping;

use Axytos\ECommerce\DataTransferObjects\BasketPositionDtoCollection;
use Axytos\KaufAufRechnung\ProductInformation\ProductVariantResolver;
use Magento\Sales\Api\Data\OrderInterface;

class BasketPositionDtoCollectionFactory
{
    /**
     * @var BasketPositionDtoFactory
     */
    private $basketPositionDtoFactory;
    /**
     * @var ProductVariantResolver
     */
    private $productVariantResolver;

    public function __construct(
        BasketPositionDtoFactory $basketPositionDtoFactory,
        ProductVariantResolver $productVariantResolver
    ) {
        $this->basketPositionDtoFactory = $basketPositionDtoFactory;
        $this->productVariantResolver = $productVariantResolver;
    }

    public function create(OrderInterface $order): BasketPositionDtoCollection
    {
        $productVariantResolution = $this->productVariantResolver->resolveProductVariants($order);

        $positions = array_map(function ($itemResolution) {
            /** @var \Magento\Sales\Api\Data\OrderItemInterface $orderItem */
            $orderItem = $itemResolution['item'];
            /** @var \Axytos\KaufAufRechnung\ProductInformation\ProductInformationInterface $productInfo */
            $productInfo = $itemResolution['product'];

            return $this->basketPositionDtoFactory->create($orderItem, $productInfo);
        }, $productVariantResolution);

        $positions = array_values($positions);

        $voucherPosition = $this->basketPositionDtoFactory->createVoucherPosition($order);
        if (!is_null($voucherPosition)) {
            if (0.0 !== $voucherPosition->grossPositionTotal) {
                array_push($positions, $voucherPosition);
            }
        }

        array_push($positions, $this->basketPositionDtoFactory->createShippingPosition($order));

        return new BasketPositionDtoCollection(...$positions);
    }
}
