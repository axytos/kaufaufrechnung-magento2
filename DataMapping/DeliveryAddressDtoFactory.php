<?php declare(strict_types=1);

namespace Axytos\KaufAufRechnung\DataMapping;

use Axytos\ECommerce\DataTransferObjects\DeliveryAddressDto;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;

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
        if(!is_null($street) && current($street)) {
            $deliveryAddressDto->addressLine1 = current($street);
        }
        return $deliveryAddressDto;
    }

    private function getShippingAddress(OrderInterface $order): OrderAddressInterface
    {
        $shippingAddress = $order->getBillingAddress();

        /** @phpstan-ignore-next-line */
        $shippingAssignments = $order->getExtensionAttributes()->getShippingAssignments();

        if (!empty($shippingAssignments))
        {
            $shippingAssingment = current($shippingAssignments);
            $shippingAddress = $shippingAssingment->getShipping()->getAddress();
        }

        return $shippingAddress;
    }

}