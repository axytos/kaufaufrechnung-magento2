<?php

namespace Axytos\KaufAufRechnung\Adapter\Database;

use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Database\DatabaseTransactionFactoryInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Database\DatabaseTransactionInterface;
use Magento\Framework\App\ResourceConnection;

class DatabaseTransactionFactory implements DatabaseTransactionFactoryInterface
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @return DatabaseTransactionInterface
     */
    public function create()
    {
        return new DatabaseTransaction($this->resourceConnection->getConnection());
    }
}
