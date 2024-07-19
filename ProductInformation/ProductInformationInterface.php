<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\ProductInformation;

interface ProductInformationInterface
{
    /**
     * @return int|null
     */
    public function getId(): int|null;

    /**
     * @return string
     */
    public function getSku(): string;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function getCategory(): string;

    /**
     * @return string[]
     */
    public function getCategoryNames(): array;

    /**
     * @return bool
     */
    public function isConfigurable(): bool;

    /**
     * @return \Magento\Catalog\Api\Data\ProductInterface[]
     */
    public function getVariants(): array;
}
