<?php

namespace Axytos\KaufAufRechnung\Adapter;

use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\OrderSyncRepositoryInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\PluginOrderInterface;
use Axytos\KaufAufRechnung\Model\Constants;
use Axytos\KaufAufRechnung\Model\Data\AxytosOrderAttributesLoader;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderSyncRepository implements OrderSyncRepositoryInterface
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Axytos\KaufAufRechnung\Adapter\PluginOrderFactory
     */
    private $pluginOrderFactory;

    /**
     * @var \Axytos\KaufAufRechnung\Model\Data\AxytosOrderAttributesLoader
     */
    private $axytosOrderAttributesLoader;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param PluginOrderFactory $pluginOrderFactory
     * @param AxytosOrderAttributesLoader $axytosOrderAttributesLoader
     * @return void
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        PluginOrderFactory $pluginOrderFactory,
        AxytosOrderAttributesLoader $axytosOrderAttributesLoader
    ) {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->pluginOrderFactory = $pluginOrderFactory;
        $this->axytosOrderAttributesLoader = $axytosOrderAttributesLoader;
    }

    /**
     * @param string[] $orderStates
     * @param int|null $limit
     * @param string|null $startId
     * @return PluginOrderInterface[]
     */
    public function getOrdersByStates($orderStates, $limit = null, $startId = null)
    {
        /** @var array<int> */
        $orderEntityIds = $this->axytosOrderAttributesLoader->getOrderEntityIdsByStates($orderStates, $limit, $startId);

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(OrderInterface::ENTITY_ID, $orderEntityIds, 'in')
            ->create();

        $orderList = $this->orderRepository->getList($searchCriteria);

        $orders = $this->filterOrdersWithAxytosPaymentMethod($orderList->getItems());
        return $this->pluginOrderFactory->createMany($orders);
    }

    /**
     * @param string|int $orderNumber
     * @return PluginOrderInterface|null
     */
    public function getOrderByOrderNumber($orderNumber)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(OrderInterface::INCREMENT_ID, strval($orderNumber))
            ->create();

        $orderList = $this->orderRepository->getList($searchCriteria);
        $orders = $this->filterOrdersWithAxytosPaymentMethod($orderList->getItems());

        if (count($orders) === 0) {
            return null;
        }

        return $this->pluginOrderFactory->create($orders[0]);
    }

    /**
     * @param array<OrderInterface> $orders
     * @return array<OrderInterface>
     */
    private function filterOrdersWithAxytosPaymentMethod(array $orders): array
    {
        // use magento lazy loading to get the payment method
        // OrderInterface::PAYMENT is not in table sales_order, but in sales_order_payment
        // so we would need to join the tables or expose extension attribute to get the payment method
        // see:
        // - https://www.magevision.com/blog/post/how-to-get-orders-by-payment-method-magento-2
        // - https://magento.stackexchange.com/a/314101
        // - https://magento.stackexchange.com/a/323019
        return array_filter($orders, function (OrderInterface $order) {
            return !is_null($order->getPayment())
                && $order->getPayment()->getMethod() === Constants::PAYMENT_METHOD_CODE;
        });
    }
}
