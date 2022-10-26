<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\DataMapping;

use Axytos\ECommerce\DataTransferObjects\RefundBasketTaxGroupDto;
use Axytos\ECommerce\DataTransferObjects\RefundBasketTaxGroupDtoCollection;
use Magento\Sales\Api\Data\CreditmemoInterface;

class RefundBasketTaxGroupDtoCollectionFactory
{
    private RefundBasketTaxGroupDtoFactory $refundBasketTaxGroupDtoFactory;

    public function __construct(RefundBasketTaxGroupDtoFactory $refundBasketTaxGroupDtoFactory)
    {
        $this->refundBasketTaxGroupDtoFactory = $refundBasketTaxGroupDtoFactory;
    }

    public function create(CreditmemoInterface $creditmemo): RefundBasketTaxGroupDtoCollection
    {
        $positionTaxValues = array_map([$this->refundBasketTaxGroupDtoFactory, 'create'], $creditmemo->getItems());
        $positionTaxValues[] = $this->refundBasketTaxGroupDtoFactory->createShippingPosition($creditmemo);

        $taxGroups = array_values(
            array_reduce(
                $positionTaxValues,
                function (array $agg, RefundBasketTaxGroupDto $cur) {
                    if (array_key_exists("$cur->taxPercent", $agg)) {
                        $agg["$cur->taxPercent"]->total += $cur->total;
                        $agg["$cur->taxPercent"]->valueToTax += $cur->valueToTax;
                    } else {
                        $agg["$cur->taxPercent"] = $cur;
                    }
                    return $agg;
                },
                []
            )
        );
        return new RefundBasketTaxGroupDtoCollection(...$taxGroups);
    }
}
