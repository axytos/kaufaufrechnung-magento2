<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\DataMapping;

use Axytos\ECommerce\DataTransferObjects\InvoiceAddressDto;
use Magento\Sales\Api\Data\OrderInterface;

class InvoiceAddressDtoFactory
{
    public function create(OrderInterface $order): InvoiceAddressDto
    {
        /** @var \Magento\Sales\Api\Data\OrderAddressInterface */
        $orderBillingAddress = $order->getBillingAddress();

        $invoiceAddressDto = new InvoiceAddressDto();
        $invoiceAddressDto->company = $orderBillingAddress->getCompany();
        $invoiceAddressDto->salutation = null;
        $invoiceAddressDto->firstname = $orderBillingAddress->getFirstname();
        $invoiceAddressDto->lastname = $orderBillingAddress->getLastname();
        $invoiceAddressDto->zipCode = $orderBillingAddress->getPostcode();
        $invoiceAddressDto->city = $orderBillingAddress->getCity();
        $invoiceAddressDto->region = $orderBillingAddress->getRegion();
        $invoiceAddressDto->country = $orderBillingAddress->getCountryId();
        $invoiceAddressDto->vatId = $orderBillingAddress->getVatId();
        $street = $orderBillingAddress->getStreet();
        if (!is_null($street) && current($street)) {
            $invoiceAddressDto->addressLine1 = current($street);
        }
        return $invoiceAddressDto;
    }
}
