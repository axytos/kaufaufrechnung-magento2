<?php

namespace Axytos\KaufAufRechnung\Adapter;

use Axytos\KaufAufRechnung\Adapter\HashCalculation\HashCalculator;
use Axytos\KaufAufRechnung\Core\InvoiceOrderContextFactory;
use Axytos\KaufAufRechnung\Core\OrderStateMachine;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class PluginOrderFactory
{
    /**
     * @var InvoiceOrderContextFactory
     */
    private $invoiceOrderContextFactory;

    /**
     * @var HashCalculator
     */
    private $hashCalculator;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OrderStateMachine
     */
    private $orderStateMachine;

    public function __construct(
        InvoiceOrderContextFactory $invoiceOrderContextFactory,
        HashCalculator $hashCalculator,
        ObjectManagerInterface $objectManager,
        OrderRepositoryInterface $orderRepository,
        OrderStateMachine $orderStateMachine
    ) {
        $this->invoiceOrderContextFactory = $invoiceOrderContextFactory;
        $this->hashCalculator = $hashCalculator;
        $this->objectManager = $objectManager;
        $this->orderRepository = $orderRepository;
        $this->orderStateMachine = $orderStateMachine;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     *
     * @return \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\PluginOrderInterface
     */
    public function create($order)
    {
        $magentoOrderInfo = $this->loadMagentoSalesOrder($order);

        return new PluginOrder(
            $magentoOrderInfo,
            $this->invoiceOrderContextFactory,
            $this->hashCalculator,
            $this->orderRepository,
            $this->orderStateMachine
        );
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface[] $orders
     *
     * @return \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\PluginOrderInterface[]
     */
    public function createMany($orders)
    {
        return array_map([$this, 'create'], $orders);
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     *
     * @return MagentoSalesOrder
     */
    private function loadMagentoSalesOrder($order)
    {
        // (1) reload extension attributes via repository plugin
        // (2) load concrete order model
        //     Order has methods to load documents for invoice, creditmemo, shipment, etc.
        //     OrderInterface does not have these methods
        //     but loadByIncrementId does not reload extension attributes via repository plugin
        // (3) set loaded extension attributes

        /** @phpstan-ignore-next-line */
        $order = $this->orderRepository->get($order->getEntityId());

        /** @var Order */
        $orderModel = $this->objectManager->create(Order::class);
        $orderModel = $orderModel->loadByIncrementId(strval($order->getIncrementId()));

        /** @phpstan-ignore-next-line */
        $orderModel->setExtensionAttributes($order->getExtensionAttributes());

        return new MagentoSalesOrder($orderModel);
    }
}
