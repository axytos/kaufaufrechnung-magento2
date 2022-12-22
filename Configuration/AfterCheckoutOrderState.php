<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\Configuration;

use Magento\Sales\Model\Order;

class AfterCheckoutOrderState
{
    public const ORDER_STATE_NEW = 'ORDER_STATE_NEW';
    public const ORDER_STATE_PENDING_PAYMENT = 'ORDER_STATE_PENDING_PAYMENT';
    public const ORDER_STATE_PROCESSING = 'ORDER_STATE_PROCESSING';
    public const ORDER_STATE_COMPLETE = 'ORDER_STATE_COMPLETE';
    public const ORDER_STATE_CLOSED = 'ORDER_STATE_CLOSED';
    public const ORDER_STATE_CANCELED = 'ORDER_STATE_CANCELED';
    public const ORDER_STATE_HOLDED = 'ORDER_STATE_HOLDED';
    public const ORDER_STATE_PAYMENT_REVIEW = 'ORDER_STATE_PAYMENT_REVIEW';

    /**
     * @var string
     */
    private static $default = Order::STATE_NEW;

    /**
     * @var array<string,string>
     */
    private static $orderStatusMapping = [
        self::ORDER_STATE_NEW => Order::STATE_NEW,
        self::ORDER_STATE_PENDING_PAYMENT => Order::STATE_PENDING_PAYMENT,
        self::ORDER_STATE_PROCESSING => Order::STATE_PROCESSING,
        self::ORDER_STATE_COMPLETE => Order::STATE_COMPLETE,
        self::ORDER_STATE_CLOSED => Order::STATE_CLOSED,
        self::ORDER_STATE_CANCELED => Order::STATE_CANCELED,
        self::ORDER_STATE_HOLDED => Order::STATE_HOLDED,
        self::ORDER_STATE_PAYMENT_REVIEW => Order::STATE_PAYMENT_REVIEW,
    ];

    /**
     * @var string
     */
    private $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function getOrderState(): string
    {
        if (!isset(self::$orderStatusMapping[$this->value])) {
            return self::$default;
        }

        return self::$orderStatusMapping[$this->value];
    }
}
