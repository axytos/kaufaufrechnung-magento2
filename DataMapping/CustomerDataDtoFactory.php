<?php declare(strict_types=1);

namespace Axytos\KaufAufRechnung\DataMapping;

use Axytos\ECommerce\DataTransferObjects\CustomerDataDto;
use DateTimeImmutable;
use Magento\Sales\Api\Data\OrderInterface;

class CustomerDataDtoFactory
{
    public function create(OrderInterface $order): CustomerDataDto
    {
        $personalDataDto = new CustomerDataDto();
        $personalDataDto->externalCustomerId = $this->getExternalCustomerId($order);
        $personalDataDto->dateOfBirth = is_null($order->getCustomerDob()) ? null : new DateTimeImmutable($order->getCustomerDob());
        $personalDataDto->email = $order->getCustomerEmail();
        return $personalDataDto;
    }

    private function getExternalCustomerId(OrderInterface $order): string
    {
        if (boolval($order->getCustomerIsGuest()) || is_null($order->getCustomerId()))
        {
            return $order->getCustomerEmail();
        }

        return strval($order->getCustomerId());
    }
}