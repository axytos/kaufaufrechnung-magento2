<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\DataMapping;

use Axytos\ECommerce\DataTransferObjects\RefundBasketTaxGroupDto;
use Axytos\ECommerce\DataTransferObjects\RefundBasketTaxGroupDtoCollection;
use Axytos\KaufAufRechnung\ProductInformation\ProductVariantResolver;
use Magento\Sales\Api\Data\CreditmemoInterface;

class RefundBasketTaxGroupDtoCollectionFactory
{
    /**
     * @var RefundBasketTaxGroupDtoFactory
     */
    private $refundBasketTaxGroupDtoFactory;

    /**
     * @var ProductVariantResolver
     */
    private $productVariantResolver;

    public function __construct(
        RefundBasketTaxGroupDtoFactory $refundBasketTaxGroupDtoFactory,
        ProductVariantResolver $productVariantResolver
    ) {
        $this->refundBasketTaxGroupDtoFactory = $refundBasketTaxGroupDtoFactory;
        $this->productVariantResolver = $productVariantResolver;
    }

    public function create(CreditmemoInterface $creditmemo): RefundBasketTaxGroupDtoCollection
    {
        $productVariantResolution = $this->productVariantResolver->resolveProductVariants($creditmemo);

        $positionTaxValues = array_map(function ($itemResolution) {
            /** @var \Magento\Sales\Api\Data\CreditmemoItemInterface $creditmemoItem */
            $creditmemoItem = $itemResolution['item'];

            return $this->refundBasketTaxGroupDtoFactory->create($creditmemoItem);
        }, $productVariantResolution);

        $positionTaxValues = array_values($positionTaxValues);

        $positionTaxValues[] = $this->refundBasketTaxGroupDtoFactory->createShippingPosition($creditmemo);

        $taxGroups = array_values(
            array_reduce(
                $positionTaxValues,
                function (array $agg, RefundBasketTaxGroupDto $cur) {
                    if (array_key_exists("{$cur->taxPercent}", $agg)) {
                        $agg["{$cur->taxPercent}"]->total += $cur->total;
                        $agg["{$cur->taxPercent}"]->valueToTax += $cur->valueToTax;
                    } else {
                        $agg["{$cur->taxPercent}"] = $cur;
                    }

                    return $agg;
                },
                []
            )
        );

        return new RefundBasketTaxGroupDtoCollection(...$taxGroups);
    }
}
