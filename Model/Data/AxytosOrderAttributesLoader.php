<?php

namespace Axytos\KaufAufRechnung\Model\Data;

use Exception;
use DomainException;
use LogicException;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\TestFramework\Utility\ChildrenClassesSearch\A;
use Zend_Db_Select_Exception;

class AxytosOrderAttributesLoader
{
    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\EntityManager\EntityManager
     */
    private $entityManager;

    public function __construct(
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection,
        ObjectManagerInterface $objectManager,
        EntityManager $entityManager
    ) {
        $this->metadataPool = $metadataPool;
        $this->resourceConnection = $resourceConnection;
        $this->objectManager = $objectManager;
        $this->entityManager = $entityManager;
    }

    /**
     * @param OrderInterface $order
     * @return AxytosOrderAttributesInterface|null
     */
    public function loadOrderAttributes(OrderInterface $order)
    {
        $metadata = $this->metadataPool->getMetadata(AxytosOrderAttributes::class);
        $connection = $this->resourceConnection->getConnection();

        $select = $connection->select()
            ->from($metadata->getEntityTable(), AxytosOrderAttributesInterface::ID)
            ->where(AxytosOrderAttributesInterface::MAGENTO_ORDER_ENTITY_ID . ' = ?', $order->getEntityId());

        $id = strval($connection->fetchOne($select));

        if ($id === '') {
            return null;
        }

        /** @var AxytosOrderAttributesInterface */
        $axytosOrderAttributes = $this->objectManager->create(AxytosOrderAttributesInterface::class);
        $this->entityManager->load($axytosOrderAttributes, $id);
        return $axytosOrderAttributes;
    }

    /**
     * @param string[] $orderStates
     * @param int|null $limit
     * @param string|null $startId
     * @return array<int>
     */
    public function getOrderEntityIdsByStates($orderStates, $limit = null, $startId = null)
    {
        $metadata = $this->metadataPool->getMetadata(AxytosOrderAttributes::class);
        $connection = $this->resourceConnection->getConnection();

        $select = $connection->select()
            ->from($metadata->getEntityTable(), AxytosOrderAttributesInterface::MAGENTO_ORDER_ENTITY_ID)
            ->where(AxytosOrderAttributesInterface::ORDER_STATE . ' IN (?)', $orderStates)
            ->order(AxytosOrderAttributesInterface::MAGENTO_ORDER_INCREMENT_ID)
            ->limit($limit);

        if (!is_null($startId)) {
            $select->where(AxytosOrderAttributesInterface::MAGENTO_ORDER_INCREMENT_ID . ' > ?', $startId);
        }

        return $connection->fetchCol($select);
    }
}
