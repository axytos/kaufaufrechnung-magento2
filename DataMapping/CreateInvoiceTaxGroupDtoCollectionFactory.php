<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\DataMapping;

use Axytos\ECommerce\DataTransferObjects\CreateInvoiceTaxGroupDto;
use Axytos\ECommerce\DataTransferObjects\CreateInvoiceTaxGroupDtoCollection;
use Axytos\KaufAufRechnung\ProductInformation\ProductVariantResolver;
use Magento\Sales\Api\Data\InvoiceInterface;

class CreateInvoiceTaxGroupDtoCollectionFactory
{
    /**
     * @var CreateInvoiceTaxGroupDtoFactory
     */
    private $createInvoiceTaxGroupDtoFactory;

    /**
     * @var ProductVariantResolver
     */
    private $productVariantResolver;

    public function __construct(
        CreateInvoiceTaxGroupDtoFactory $createInvoiceTaxGroupDtoFactory,
        ProductVariantResolver $productVariantResolver
    ) {
        $this->createInvoiceTaxGroupDtoFactory = $createInvoiceTaxGroupDtoFactory;
        $this->productVariantResolver = $productVariantResolver;
    }

    public function create(InvoiceInterface $invoice): CreateInvoiceTaxGroupDtoCollection
    {
        $productVariantResolution = $this->productVariantResolver->resolveProductVariants($invoice);

        $positionTaxValues = array_map(function ($itemResolution) {
            /** @var \Magento\Sales\Api\Data\InvoiceItemInterface $invoiceItem */
            $invoiceItem = $itemResolution['item'];

            return $this->createInvoiceTaxGroupDtoFactory->create($invoiceItem);
        }, $productVariantResolution);

        $positionTaxValues = array_values($positionTaxValues);

        $positionTaxValues[] = $this->createInvoiceTaxGroupDtoFactory->createShippingPosition($invoice);

        $taxGroups = array_values(
            array_reduce(
                $positionTaxValues,
                function (array $agg, CreateInvoiceTaxGroupDto $cur) {
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

        return new CreateInvoiceTaxGroupDtoCollection(...$taxGroups);
    }
}
