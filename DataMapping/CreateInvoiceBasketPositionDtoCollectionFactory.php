<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\DataMapping;

use Axytos\ECommerce\DataTransferObjects\CreateInvoiceBasketPositionDtoCollection;
use Axytos\KaufAufRechnung\ProductInformation\ProductVariantResolver;
use Magento\Sales\Api\Data\InvoiceInterface;

class CreateInvoiceBasketPositionDtoCollectionFactory
{
    /**
     * @var \Axytos\KaufAufRechnung\DataMapping\CreateInvoiceBasketPositionDtoFactory
     */
    private $createInvoiceBasketPositionDtoFactory;

    /**
     * @var \Axytos\KaufAufRechnung\ProductInformation\ProductVariantResolver
     */
    private $productVariantResolver;

    public function __construct(
        CreateInvoiceBasketPositionDtoFactory $createInvoiceBasketPositionDtoFactory,
        ProductVariantResolver $productVariantResolver
    ) {
        $this->createInvoiceBasketPositionDtoFactory = $createInvoiceBasketPositionDtoFactory;
        $this->productVariantResolver = $productVariantResolver;
    }

    public function create(InvoiceInterface $invoice): CreateInvoiceBasketPositionDtoCollection
    {
        $productVariantResolution = $this->productVariantResolver->resolveProductVariants($invoice);

        $positions = array_map(function ($itemResolution) {
            /** @var \Magento\Sales\Api\Data\InvoiceItemInterface $invoiceItem */
            $invoiceItem = $itemResolution['item'];
            /** @var \Axytos\KaufAufRechnung\ProductInformation\ProductInformationInterface $productInfo */
            $productInfo = $itemResolution['product'];
            return $this->createInvoiceBasketPositionDtoFactory->create($invoiceItem, $productInfo);
        }, $productVariantResolution);

        $positions = array_values($positions);

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
}
