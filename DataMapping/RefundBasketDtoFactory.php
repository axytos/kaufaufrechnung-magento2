<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\DataMapping;

use Axytos\ECommerce\DataTransferObjects\RefundBasketDto;
use Magento\Sales\Api\Data\CreditmemoInterface;

class RefundBasketDtoFactory
{
    /**
     * @var \Axytos\KaufAufRechnung\DataMapping\RefundBasketPositionDtoCollectionFactory
     */
    private $refundBasketPositionDtoCollectionFactory;
    /**
     * @var \Axytos\KaufAufRechnung\DataMapping\RefundBasketTaxGroupDtoCollectionFactory
     */
    private $refundBasketTaxGroupDtoCollectionFactory;

    public function __construct(
        RefundBasketPositionDtoCollectionFactory $refundBasketPositionDtoCollectionFactory,
        RefundBasketTaxGroupDtoCollectionFactory $refundBasketTaxGroupDtoCollectionFactory
    ) {
        $this->refundBasketPositionDtoCollectionFactory = $refundBasketPositionDtoCollectionFactory;
        $this->refundBasketTaxGroupDtoCollectionFactory = $refundBasketTaxGroupDtoCollectionFactory;
    }

    public function create(CreditmemoInterface $creditmemo): RefundBasketDto
    {
        $refundBasket = new RefundBasketDto();
        $refundBasket->positions = $this->refundBasketPositionDtoCollectionFactory->create($creditmemo);
        $refundBasket->taxGroups = $this->refundBasketTaxGroupDtoCollectionFactory->create($creditmemo);
        $refundBasket->grossTotal = floatval($creditmemo->getGrandTotal());
        $refundBasket->netTotal = floatval($creditmemo->getGrandTotal()) - floatval($creditmemo->getTaxAmount());
        return $refundBasket;
    }
}
