<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\ProductInformation;

interface ProductInformationInterface
{
    public function getId(): ?int;

    public function getSku(): string;

    public function getName(): string;

    public function getCategory(): string;

    /**
     * @return string[]
     */
    public function getCategoryNames(): array;

    public function isConfigurable(): bool;

    /**
     * @return \Magento\Catalog\Api\Data\ProductInterface[]
     */
    public function getVariants(): array;
}
