<?php

namespace Axytos\KaufAufRechnung\Adapter\Information;

use Axytos\KaufAufRechnung\Core\InvoiceOrderContext;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\PaymentInformationInterface;

/**
 * payment callbacks are currently not a supported feature for magento.
 */
class PaymentInformation implements PaymentInformationInterface
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
}
