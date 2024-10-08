<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\Tests\Unit\Configuration;

use Axytos\KaufAufRechnung\Configuration\AfterCheckoutOrderState;
use Magento\Sales\Model\Order;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class AfterCheckoutOrderStateTest extends TestCase
{
    /**
     * @dataProvider getOrderStateTestCases
     */
    #[DataProvider('getOrderStateTestCases')]
    public function test_get_order_state_returns_correct_value(string $value, string $expectedValue): void
    {
        $afterCheckoutOrderState = new AfterCheckoutOrderState($value);

        $this->assertEquals($expectedValue, $afterCheckoutOrderState->getOrderState());
    }

    /**
     * @return array<array<mixed>>
     */
    public static function getOrderStateTestCases(): array
    {
        return [
            [AfterCheckoutOrderState::ORDER_STATE_NEW, Order::STATE_NEW],
            [AfterCheckoutOrderState::ORDER_STATE_PENDING_PAYMENT, Order::STATE_PENDING_PAYMENT],
            [AfterCheckoutOrderState::ORDER_STATE_PROCESSING, Order::STATE_PROCESSING],
            [AfterCheckoutOrderState::ORDER_STATE_COMPLETE, Order::STATE_COMPLETE],
            [AfterCheckoutOrderState::ORDER_STATE_CLOSED, Order::STATE_CLOSED],
            [AfterCheckoutOrderState::ORDER_STATE_CANCELED, Order::STATE_CANCELED],
            [AfterCheckoutOrderState::ORDER_STATE_HOLDED, Order::STATE_HOLDED],
            [AfterCheckoutOrderState::ORDER_STATE_PAYMENT_REVIEW, Order::STATE_PAYMENT_REVIEW],
        ];
    }

    public function test_get_order_state_returns_actio_n_reope_n_as_default(): void
    {
        $afterCheckoutOrderStatus = new AfterCheckoutOrderState('');

        $this->assertEquals(Order::STATE_NEW, $afterCheckoutOrderStatus->getOrderState());
    }
}
