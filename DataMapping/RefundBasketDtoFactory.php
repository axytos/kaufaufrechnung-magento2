<?php declare(strict_types=1);

namespace Axytos\KaufAufRechnung\DataMapping;

use Axytos\ECommerce\DataTransferObjects\RefundBasketDto;
use Magento\Sales\Api\Data\CreditmemoInterface;

class RefundBasketDtoFactory
{
    private RefundBasketPositionDtoCollectionFactory $refundBasketPositionDtoCollectionFactory;
    private RefundBasketTaxGroupDtoCollectionFactory $refundBasketTaxGroupDtoCollectionFactory;

    public function __construct(
        RefundBasketPositionDtoCollectionFactory $refundBasketPositionDtoCollectionFactory,
        RefundBasketTaxGroupDtoCollectionFactory $refundBasketTaxGroupDtoCollectionFactory
    )
    {
        $this->refundBasketPositionDtoCollectionFactory = $refundBasketPositionDtoCollectionFactory;
        $this->refundBasketTaxGroupDtoCollectionFactory = $refundBasketTaxGroupDtoCollectionFactory;
    }

    public function create(CreditmemoInterface $creditmemo): RefundBasketDto
    {
        $refundBasket = new RefundBasketDto();
        $refundBasket->positions = $this->refundBasketPositionDtoCollectionFactory->create($creditmemo);
        $refundBasket->taxGroups = $this->refundBasketTaxGroupDtoCollectionFactory->create($creditmemo);
        $refundBasket->grossTotal = $creditmemo->getGrandTotal();
        $refundBasket->netTotal = $creditmemo->getGrandTotal() - $creditmemo->getTaxAmount();
        return $refundBasket;
    }
}