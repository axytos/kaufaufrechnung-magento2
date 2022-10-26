<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\DataMapping;

use Axytos\ECommerce\DataTransferObjects\RefundBasketPositionDto;
use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;

class RefundBasketPositionDtoFactory
{
    public function create(CreditmemoItemInterface $creditmemoItem): RefundBasketPositionDto
    {
        $position = new RefundBasketPositionDto();
        $position->productId = strval($creditmemoItem->getProductId());
        $position->grossRefundTotal = floatval($creditmemoItem->getPriceInclTax());
        $position->netRefundTotal = floatval($creditmemoItem->getPrice());
        return $position;
    }

    public function createShippingPosition(CreditmemoInterface $creditmemo): RefundBasketPositionDto
    {
        $position = new RefundBasketPositionDto();
        $position->productId = '0';
        $position->grossRefundTotal = floatval($creditmemo->getShippingInclTax());
        $position->netRefundTotal = floatval($creditmemo->getShippingAmount());
        return $position;
    }
}
