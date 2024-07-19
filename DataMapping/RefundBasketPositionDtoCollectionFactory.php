<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\DataMapping;

use Axytos\ECommerce\DataTransferObjects\RefundBasketPositionDtoCollection;
use Axytos\KaufAufRechnung\ProductInformation\ProductVariantResolver;
use Magento\Sales\Api\Data\CreditmemoInterface;

class RefundBasketPositionDtoCollectionFactory
{
    /**
     * @var \Axytos\KaufAufRechnung\DataMapping\RefundBasketPositionDtoFactory
     */
    private $refundBasketPositionDtoFactory;

    /**
     * @var \Axytos\KaufAufRechnung\ProductInformation\ProductVariantResolver
     */
    private $productVariantResolver;

    public function __construct(
        RefundBasketPositionDtoFactory $refundBasketPositionDtoFactory,
        ProductVariantResolver $productVariantResolver
    ) {
        $this->refundBasketPositionDtoFactory = $refundBasketPositionDtoFactory;
        $this->productVariantResolver = $productVariantResolver;
    }

    public function create(CreditmemoInterface $creditmemo): RefundBasketPositionDtoCollection
    {
        $productVariantResolution = $this->productVariantResolver->resolveProductVariants($creditmemo);

        $positions = array_map(function ($itemResolution) {
            /** @var \Magento\Sales\Api\Data\CreditmemoItemInterface $creditmemoItem */
            $creditmemoItem = $itemResolution['item'];
            /** @var \Axytos\KaufAufRechnung\ProductInformation\ProductInformationInterface $productInfo */
            $productInfo = $itemResolution['product'];
            return $this->refundBasketPositionDtoFactory->create($creditmemoItem, $productInfo);
        }, $productVariantResolution);

        $positions = array_values($positions);

        array_push($positions, $this->refundBasketPositionDtoFactory->createShippingPosition($creditmemo));

        return new RefundBasketPositionDtoCollection(...$positions);
    }
}
