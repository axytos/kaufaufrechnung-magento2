<?php

namespace Axytos\KaufAufRechnung\Plugin;

use Axytos\KaufAufRechnung\Model\Constants;
use Axytos\KaufAufRechnung\Model\Data\AxytosOrderAttributesInterface;
use Axytos\KaufAufRechnung\Model\Data\AxytosOrderAttributesLoader;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderRepositoryPlugin
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var AxytosOrderAttributesLoader
     */
    private $axytosOrderAttributesLoader;

    /**
     * @return void
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        EntityManager $entityManager,
        AxytosOrderAttributesLoader $axytosOrderAttributesLoader
    ) {
        $this->objectManager = $objectManager;
        $this->entityManager = $entityManager;
        $this->axytosOrderAttributesLoader = $axytosOrderAttributesLoader;
    }

    /**
     * @return OrderInterface
     */
    public function afterSave(OrderRepositoryInterface $subject, OrderInterface $order)
    {
        $this->saveAxytosOrderAttributes($order);

        return $order;
    }

    /**
     * @return OrderInterface
     */
    public function afterGet(OrderRepositoryInterface $subject, OrderInterface $order)
    {
        $this->loadAxytosOrderAttributes($order);

        return $order;
    }

    /**
     * @return OrderSearchResultInterface
     */
    public function afterGetList(OrderRepositoryInterface $subject, OrderSearchResultInterface $searchCriteria)
    {
        foreach ($searchCriteria->getItems() as $order) {
            $this->loadAxytosOrderAttributes($order);
        }

        return $searchCriteria;
    }

    /**
     * @return void
     */
    private function saveAxytosOrderAttributes(OrderInterface $order)
    {
        if (!$this->isAxytosOrder($order)) {
            return;
        }

        $extensionAttributes = $order->getExtensionAttributes();
        if (!is_null($extensionAttributes)) {
            /**
             * @phpstan-ignore-next-line because extension interface is generated by magento2
             *
             * @var AxytosOrderAttributesInterface */
            $axytosOrderAttributes = $extensionAttributes->getAxytosKaufaufrechnungOrderAttributes();

            // if the order attributes are not loaded, check if they exist in the database to prevent duplicate entries
            // creating a credit memo in the admin panel will trigger the save method without loading the order attributes
            // so we need to check if order attributes already exist in the database
            if (is_null($axytosOrderAttributes)) {
                $axytosOrderAttributes = $this->axytosOrderAttributesLoader->loadOrderAttributes($order);
            }

            // If the order attributes are still not loaded, create a new instance
            if (is_null($axytosOrderAttributes)) {
                /** @var AxytosOrderAttributesInterface */
                $axytosOrderAttributes = $this->objectManager->create(AxytosOrderAttributesInterface::class);
                $axytosOrderAttributes->setMagentoOrderEntityId($order->getEntityId());
                $axytosOrderAttributes->setMagentoOrderIncrementId($order->getIncrementId());
            }

            $this->entityManager->save($axytosOrderAttributes);
            /** @phpstan-ignore-next-line because extension interface is generated by magento2 */
            $extensionAttributes->setAxytosKaufaufrechnungOrderAttributes($axytosOrderAttributes);
        }
    }

    /**
     * @return void
     */
    private function loadAxytosOrderAttributes(OrderInterface $order)
    {
        if (!$this->isAxytosOrder($order)) {
            return;
        }

        $extensionAttributes = $order->getExtensionAttributes();
        if (is_null($extensionAttributes)) {
            return;
        }

        $axytosOrderAttributes = $this->axytosOrderAttributesLoader->loadOrderAttributes($order);
        if (is_null($axytosOrderAttributes)) {
            return;
        }

        /** @phpstan-ignore-next-line because extension interface is generated by magento2 */
        $extensionAttributes->setAxytosKaufaufrechnungOrderAttributes($axytosOrderAttributes);
    }

    /**
     * @return bool
     */
    private function isAxytosOrder(OrderInterface $order)
    {
        if (is_null($order->getPayment())) {
            return false;
        }

        return Constants::PAYMENT_METHOD_CODE === $order->getPayment()->getMethod();
    }
}
