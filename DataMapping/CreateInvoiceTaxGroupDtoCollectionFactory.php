<?php declare(strict_types=1);

namespace Axytos\KaufAufRechnung\DataMapping;

use Axytos\ECommerce\DataTransferObjects\CreateInvoiceTaxGroupDto;
use Axytos\ECommerce\DataTransferObjects\CreateInvoiceTaxGroupDtoCollection;
use Magento\Sales\Api\Data\InvoiceInterface;

class CreateInvoiceTaxGroupDtoCollectionFactory
{
    private CreateInvoiceTaxGroupDtoFactory $createInvoiceTaxGroupDtoFactory;

    public function __construct(CreateInvoiceTaxGroupDtoFactory $createInvoiceTaxGroupDtoFactory)
    {
        $this->createInvoiceTaxGroupDtoFactory = $createInvoiceTaxGroupDtoFactory;
    }

    public function create(InvoiceInterface $invoice): CreateInvoiceTaxGroupDtoCollection
    {
        $taxGroups = array_values(
            array_reduce(
                array_map([$this->createInvoiceTaxGroupDtoFactory, 'create'], $this->getItemsArray($invoice)),
                function(array $agg, CreateInvoiceTaxGroupDto $cur) {
                    if(array_key_exists("$cur->taxPercent", $agg)) {
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
        return new CreateInvoiceTaxGroupDtoCollection(...$taxGroups);
    }

    private function getItemsArray(InvoiceInterface $invoice): array
    {
        $items = [];

        foreach ($invoice->getItems() as $item) {
            $items[] = $item;
        }

        return $items;
    }
}
