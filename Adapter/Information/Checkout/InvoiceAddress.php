<?php

namespace Axytos\KaufAufRechnung\Adapter\Information\Checkout;

use Axytos\ECommerce\DataTransferObjects\InvoiceAddressDto;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\Checkout\InvoiceAddressInterface;

class InvoiceAddress implements InvoiceAddressInterface
{
    /**
     * @var InvoiceAddressDto
     */
    private $dto;

    public function __construct(InvoiceAddressDto $dto)
    {
        $this->dto = $dto;
    }

    public function getCompanyName()
    {
        return $this->dto->company;
    }

    public function getSalutation()
    {
        return $this->dto->salutation;
    }

    public function getFirstName()
    {
        return $this->dto->firstname;
    }

    public function getLastName()
    {
        return $this->dto->lastname;
    }

    public function getZipCode()
    {
        return strval($this->dto->zipCode);
    }

    public function getCityName()
    {
        return strval($this->dto->city);
    }

    public function getRegionName()
    {
        return $this->dto->region;
    }

    public function getCountryCode()
    {
        return strval($this->dto->country);
    }

    public function getVATId()
    {
        return $this->dto->vatId;
    }

    public function getStreet()
    {
        return $this->dto->addressLine1;
    }

    public function getAdditionalAddressLine2()
    {
        return $this->dto->addressLine2;
    }

    public function getAdditionalAddressLine3()
    {
        return $this->dto->addressLine3;
    }

    public function getAdditionalAddressLine4()
    {
        return $this->dto->addressLine4;
    }
}
