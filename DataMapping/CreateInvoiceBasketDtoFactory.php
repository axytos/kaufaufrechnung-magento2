<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\DataMapping;

use Axytos\ECommerce\DataTransferObjects\CreateInvoiceBasketDto;
use Magento\Sales\Api\Data\InvoiceInterface;

class CreateInvoiceBasketDtoFactory
{
    /**
     * @var CreateInvoiceBasketPositionDtoCollectionFactory
     */
    private $createInvoiceBasketPositionDtoCollectionFactory;
    /**
     * @var CreateInvoiceTaxGroupDtoCollectionFactory
     */
    private $createInvoiceTaxGroupDtoCollectionFactory;

    public function __construct(
        CreateInvoiceBasketPositionDtoCollectionFactory $createInvoiceBasketPositionDtoCollectionFactory,
        CreateInvoiceTaxGroupDtoCollectionFactory $createInvoiceTaxGroupDtoCollectionFactory
    ) {
        $this->createInvoiceBasketPositionDtoCollectionFactory = $createInvoiceBasketPositionDtoCollectionFactory;
        $this->createInvoiceTaxGroupDtoCollectionFactory = $createInvoiceTaxGroupDtoCollectionFactory;
    }

    public function create(InvoiceInterface $invoice): CreateInvoiceBasketDto
    {
        $basket = new CreateInvoiceBasketDto();
        $basket->positions = $this->createInvoiceBasketPositionDtoCollectionFactory->create($invoice);
        $basket->taxGroups = $this->createInvoiceTaxGroupDtoCollectionFactory->create($invoice);
        $basket->grossTotal = floatval($invoice->getGrandTotal());
        $basket->netTotal = floatval($invoice->getGrandTotal()) - floatval($invoice->getTaxAmount());

        return $basket;
    }
}
