<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\Core;

use Axytos\KaufAufRechnung\Configuration\PluginConfiguration;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class OrderStateMachine
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var PluginConfiguration
     */
    private $pluginConfig;

    public function __construct(OrderRepositoryInterface $orderRepository, PluginConfiguration $pluginConfig)
    {
        $this->orderRepository = $orderRepository;
        $this->pluginConfig = $pluginConfig;
    }

    public function setCanceled(OrderInterface $order): void
    {
        $this->setState($order, Order::STATE_CANCELED, 'axytos Kauf auf Rechnung: Canceled');
    }

    public function setPaymentReview(OrderInterface $order): void
    {
        $this->setState($order, Order::STATE_PAYMENT_REVIEW, 'axytos Kauf auf Rechnung: Payment Review');
    }

    public function setPendingPayment(OrderInterface $order): void
    {
        $this->setState($order, Order::STATE_PENDING_PAYMENT, 'axytos Kauf auf Rechnung: Pending Payment');
    }

    public function setTechnicalError(OrderInterface $order): void
    {
        $this->setState($order, Order::STATE_CANCELED, 'axytos Kauf auf Rechnung: Technical Error');
    }

    public function setRejected(OrderInterface $order): void
    {
        $this->setState($order, Order::STATE_CANCELED, 'axytos Kauf auf Rechnung: Rejected');
    }

    public function setComplete(OrderInterface $order): void
    {
        $this->setState($order, Order::STATE_COMPLETE, 'axytos Kauf auf Rechnung: Complete');
    }

    public function setConfiguredAfterCheckoutOrderStatus(OrderInterface $order): void
    {
        $afterCheckoutOrderState = $this->pluginConfig->getAfterCheckoutOrderState();

        $orderState = $afterCheckoutOrderState->getOrderState();
        $this->setState($order, $orderState, 'axytos Kauf auf Rechnung, Order State After Checkout: ' . $orderState);
    }

    private function setState(OrderInterface $order, string $state, string $comment): void
    {
        $order->setState($state);

        if ($order instanceof Order) {
            $order->addCommentToStatusHistory($comment, true);
        }

        $this->orderRepository->save($order);
    }
}
