<?php

namespace Axytos\KaufAufRechnung\Adapter\Database;

use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Database\DatabaseTransactionInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

class DatabaseTransaction implements DatabaseTransactionInterface
{
    /**
     * @var AdapterInterface
     */
    private $database;

    public function __construct(AdapterInterface $database)
    {
        $this->database = $database;
    }

    /**
     * @return void
     */
    public function begin()
    {
        $this->database->beginTransaction();
    }

    /**
     * @return void
     */
    public function commit()
    {
        $this->database->commit();
    }

    /**
     * @return void
     */
    public function rollback()
    {
        $this->database->rollBack();
    }
}
