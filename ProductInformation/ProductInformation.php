<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\ProductInformation;

use Magento\Catalog\Api\Data\ProductInterface;

class ProductInformation implements ProductInformationInterface
{
    const CATEGORY_SEPARATOR = ';';

    /**
     * @var ProductInterface
     */
    private $product;
    /**
     * @var string[]
     */
    private $categoryNames = [];
    /**
     * @var ProductInterface[]
     */
    private $variants = [];

    /**
     * @param string[]                                     $categoryNames
     * @param \Magento\Catalog\Api\Data\ProductInterface[] $variants
     *
     * @return void
     */
    public function __construct(ProductInterface $product, $categoryNames, $variants)
    {
        $this->product = $product;
        $this->categoryNames = $categoryNames;
        $this->variants = $variants;
    }

    public function getId(): ?int
    {
        return $this->product->getId();
    }

    public function getSku(): string
    {
        return strval($this->product->getSku());
    }

    public function getName(): string
    {
        return strval($this->product->getName());
    }

    public function getCategory(): string
    {
        return join(self::CATEGORY_SEPARATOR, $this->getCategoryNames());
    }

    public function getCategoryNames(): array
    {
        return $this->categoryNames;
    }

    public function isConfigurable(): bool
    {
        return ProductTypeCodes::CONFIGURABLE === $this->product->getTypeId();
    }

    public function getVariants(): array
    {
        return $this->variants;
    }
}
