<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\DataMapping;

use Axytos\ECommerce\DataTransferObjects\CreateInvoiceBasketPositionDtoCollection;
use Magento\Sales\Api\Data\InvoiceInterface;

class CreateInvoiceBasketPositionDtoCollectionFactory
{
    /**
     * @var \Axytos\KaufAufRechnung\DataMapping\CreateInvoiceBasketPositionDtoFactory
     */
    private $createInvoiceBasketPositionDtoFactory;

    public function __construct(CreateInvoiceBasketPositionDtoFactory $createInvoiceBasketPositionDtoFactory)
    {
        $this->createInvoiceBasketPositionDtoFactory = $createInvoiceBasketPositionDtoFactory;
    }

    public function create(InvoiceInterface $invoice): CreateInvoiceBasketPositionDtoCollection
    {
        $positions = array_map([$this->createInvoiceBasketPositionDtoFactory, 'create'], $this->getItemsArray($invoice));
        $shippingPosition = $this->createInvoiceBasketPositionDtoFactory->createShippingPosition($invoice);
        array_push($positions, $shippingPosition);

        $voucherPosition = $this->createInvoiceBasketPositionDtoFactory->createVoucherPosition($invoice);
        if (!is_null($voucherPosition)) {
            if ($voucherPosition->grossPositionTotal !== 0.0) {
                array_push($positions, $voucherPosition);
            }
        }

        return new CreateInvoiceBasketPositionDtoCollection(...$positions);
    }

    /**
     * @return \Magento\Sales\Api\Data\InvoiceItemInterface[]
     */
    private function getItemsArray(InvoiceInterface $invoice): array
    {
        $items = [];

        foreach ($invoice->getItems() as $item) {
            $items[] = $item;
        }

        return $items;
    }
}
