<?php declare(strict_types=1);

namespace Axytos\KaufAufRechnung\DataMapping;

use Axytos\ECommerce\DataTransferObjects\RefundBasketPositionDtoCollection;
use Magento\Sales\Api\Data\CreditmemoInterface;

class RefundBasketPositionDtoCollectionFactory
{
    private RefundBasketPositionDtoFactory $refundBasketPositionDtoFactory;

    public function __construct(RefundBasketPositionDtoFactory $refundBasketPositionDtoFactory)
    {
        $this->refundBasketPositionDtoFactory = $refundBasketPositionDtoFactory;
    }

    public function create(CreditmemoInterface $creditmemo): RefundBasketPositionDtoCollection
    {
        $positions = array_map([$this->refundBasketPositionDtoFactory, 'create'], $creditmemo->getItems());

        return new RefundBasketPositionDtoCollection(...$positions);
    }
}