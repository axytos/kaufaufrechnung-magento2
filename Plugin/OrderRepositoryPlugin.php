<?php

namespace Axytos\KaufAufRechnung\Plugin;

use Axytos\KaufAufRechnung\Model\Constants;
use Axytos\KaufAufRechnung\Model\Data\AxytosOrderAttributesInterface;
use Axytos\KaufAufRechnung\Model\Data\AxytosOrderAttributesLoader;
use DomainException;
use LogicException;
use Exception;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Zend_Db_Select_Exception;

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
     * @param ObjectManagerInterface $objectManager
     * @param EntityManager $entityManager
     * @param AxytosOrderAttributesLoader $axytosOrderAttributesLoader
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
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $order
     * @return OrderInterface
     * @throws LogicException
     * @throws Exception
     */
    public function afterSave(\Magento\Sales\Api\OrderRepositoryInterface $subject, \Magento\Sales\Api\Data\OrderInterface $order)
    {
        $this->saveAxytosOrderAttributes($order);
        return $order;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $order
     * @return OrderInterface
     * @throws Exception
     * @throws DomainException
     * @throws LogicException
     */
    public function afterGet(\Magento\Sales\Api\OrderRepositoryInterface $subject, \Magento\Sales\Api\Data\OrderInterface $order)
    {
        $this->loadAxytosOrderAttributes($order);
        return $order;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderSearchResultInterface $searchCriteria
     * @return OrderSearchResultInterface
     * @throws Exception
     * @throws DomainException
     * @throws LogicException
     */
    public function afterGetList(\Magento\Sales\Api\OrderRepositoryInterface $subject, \Magento\Sales\Api\Data\OrderSearchResultInterface $searchCriteria)
    {
        foreach ($searchCriteria->getItems() as $order) {
            $this->loadAxytosOrderAttributes($order);
        }
        return $searchCriteria;
    }

    /**
     * @param OrderInterface $order
     * @return void
     * @throws LogicException
     * @throws Exception
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
             * @var \Axytos\KaufAufRechnung\Model\Data\AxytosOrderAttributesInterface */
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
     * @param OrderInterface $order
     * @return void
     * @throws Exception
     * @throws DomainException
     * @throws Zend_Db_Select_Exception
     * @throws LogicException
     */
    private function loadAxytosOrderAttributes(OrderInterface $order)
    {
        if (!$this->isAxytosOrder($order)) {
            return;
        }

        $extensionAttributes = $order->getExtensionAttributes();
        if (!is_null($extensionAttributes)) {
            $axytosOrderAttributes = $this->axytosOrderAttributesLoader->loadOrderAttributes($order);
            /** @phpstan-ignore-next-line because extension interface is generated by magento2 */
            $extensionAttributes->setAxytosKaufaufrechnungOrderAttributes($axytosOrderAttributes);
        }
    }

    /**
     * @param OrderInterface $order
     * @return bool
     */
    private function isAxytosOrder(OrderInterface $order)
    {
        if (is_null($order->getPayment())) {
            return false;
        }
        return $order->getPayment()->getMethod() === Constants::PAYMENT_METHOD_CODE;
    }
}