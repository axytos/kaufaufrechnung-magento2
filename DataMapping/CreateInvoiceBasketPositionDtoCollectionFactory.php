<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\DataMapping;

use Axytos\ECommerce\DataTransferObjects\CreateInvoiceBasketPositionDtoCollection;
use Magento\Sales\Api\Data\InvoiceInterface;

class CreateInvoiceBasketPositionDtoCollectionFactory
{
    private CreateInvoiceBasketPositionDtoFactory $createInvoiceBasketPositionDtoFactory;

    public function __construct(CreateInvoiceBasketPositionDtoFactory $createInvoiceBasketPositionDtoFactory)
    {
        $this->createInvoiceBasketPositionDtoFactory = $createInvoiceBasketPositionDtoFactory;
    }

    public function create(InvoiceInterface $invoice): CreateInvoiceBasketPositionDtoCollection
    {
        $positions = array_map([$this->createInvoiceBasketPositionDtoFactory, 'create'], $this->getItemsArray($invoice));
        $shippingPosition = $this->createInvoiceBasketPositionDtoFactory->createShippingPosition($invoice);
        array_push($positions, $shippingPosition);

        return new CreateInvoiceBasketPositionDtoCollection(...$positions);
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
