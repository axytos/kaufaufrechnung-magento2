<?php

namespace Axytos\KaufAufRechnung\Adapter\Information;

use Axytos\KaufAufRechnung\Core\InvoiceOrderContext;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\BasketUpdateInformationInterface;
use Axytos\KaufAufRechnung\Adapter\Information\BasketUpdate\Basket;

class BasketUpdateInformation implements BasketUpdateInformationInterface
{
    /**
     * @var \Axytos\KaufAufRechnung\Core\InvoiceOrderContext
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

    public function getBasket()
    {
        $dto = $this->invoiceOrderContext->getBasket();
        return new Basket($dto);
    }
}
