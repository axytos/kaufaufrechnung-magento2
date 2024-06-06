<?php

namespace Axytos\KaufAufRechnung\Adapter\Information;

use Axytos\KaufAufRechnung\Core\InvoiceOrderContext;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\CancelInformationInterface;

class CancelInformation implements CancelInformationInterface
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
}
