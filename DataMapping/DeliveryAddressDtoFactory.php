<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\DataMapping;

use Axytos\ECommerce\DataTransferObjects\DeliveryAddressDto;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;

class DeliveryAddressDtoFactory
{
    public function create(OrderInterface $order): DeliveryAddressDto
    {
        $shippingAddress = $this->getShippingAddress($order);

        $deliveryAddressDto = new DeliveryAddressDto();
        $deliveryAddressDto->company = $shippingAddress->getCompany();
        $deliveryAddressDto->salutation = null;
        $deliveryAddressDto->firstname = $shippingAddress->getFirstname();
        $deliveryAddressDto->lastname = $shippingAddress->getLastname();
        $deliveryAddressDto->zipCode = $shippingAddress->getPostcode();
        $deliveryAddressDto->city = $shippingAddress->getCity();
        $deliveryAddressDto->region = $shippingAddress->getRegion();
        $deliveryAddressDto->country = $shippingAddress->getCountryId();
        $deliveryAddressDto->vatId = $shippingAddress->getVatId();
        $street = $shippingAddress->getStreet();
        if (!is_null($street) && count($street) > 0) {
            $deliveryAddressDto->addressLine1 = current($street);
        }
        if (!is_null($street) && count($street) > 1) {
            $deliveryAddressDto->addressLine2 = $street[1];
        }

        return $deliveryAddressDto;
    }

    private function getShippingAddress(OrderInterface $order): OrderAddressInterface
    {
        $shippingAddress = $order->getBillingAddress();

        /** @phpstan-ignore-next-line */
        $shippingAssignments = $order->getExtensionAttributes()->getShippingAssignments();

        if (!is_null($shippingAssignments)) {
            $shippingAssingment = current($shippingAssignments);
            $shippingAddress = $shippingAssingment->getShipping()->getAddress();
        }

        return $shippingAddress;
    }
}
