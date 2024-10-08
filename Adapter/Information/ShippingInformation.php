<?php

namespace Axytos\KaufAufRechnung\Adapter\Information;

use Axytos\KaufAufRechnung\Adapter\Information\Shipping\BasketPosition;
use Axytos\KaufAufRechnung\Core\InvoiceOrderContext;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\ShippingInformationInterface;

class ShippingInformation implements ShippingInformationInterface
{
    /**
     * @var InvoiceOrderContext
     */
    private $invoiceOrderContext;

    public function __construct(InvoiceOrderContext $invoiceOrderContext)
    {
        $this->invoiceOrderContext = $invoiceOrderContext;
    }

    public function getOrderNumber()
    {
        return $this->invoiceOrderContext->getOrderNumber();
    }

    public function getShippingBasketPositions()
    {
        $positions = $this->invoiceOrderContext->getShippingBasketPositions();

        return array_map(function ($position) {
            return new BasketPosition($position);
        }, $positions->getElements());
    }
}
