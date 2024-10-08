<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\Model\Admin;

use Axytos\KaufAufRechnung\Configuration\AfterCheckoutOrderState;
use Magento\Framework\Option\ArrayInterface;

class OrderStateAfterCheckoutDisplayOptions implements ArrayInterface
{
    /**
     * @return array<array<mixed>>
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => 'New',
                'value' => AfterCheckoutOrderState::ORDER_STATE_NEW,
            ],
            [
                'label' => 'Pending Payment',
                'value' => AfterCheckoutOrderState::ORDER_STATE_PENDING_PAYMENT,
            ],
            [
                'label' => 'Canceled',
                'value' => AfterCheckoutOrderState::ORDER_STATE_CANCELED,
            ],
            [
                'label' => 'Closed',
                'value' => AfterCheckoutOrderState::ORDER_STATE_CLOSED,
            ],
            [
                'label' => 'Complete',
                'value' => AfterCheckoutOrderState::ORDER_STATE_COMPLETE,
            ],
            [
                'label' => 'On Hold',
                'value' => AfterCheckoutOrderState::ORDER_STATE_HOLDED,
            ],
            [
                'label' => 'Processing',
                'value' => AfterCheckoutOrderState::ORDER_STATE_PROCESSING,
            ],
            [
                'label' => 'Payment Review',
                'value' => AfterCheckoutOrderState::ORDER_STATE_PAYMENT_REVIEW,
            ],
        ];
    }
}
