<?php

namespace Axytos\KaufAufRechnung\Model\Data;

class AxytosOrderAttributes implements AxytosOrderAttributesInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int|null
     */
    private $magentoOrderEntityId;

    /**
     * @var string|null
     */
    private $orderIncrementId;

    /**
     * @var string
     */
    private $orderPreCheckResult = '';

    /**
     * @var bool
     */
    private $shippingReported = false;

    /**
     * @var string|null
     */
    private $reportedTrackingCode = '';

    /**
     * @var string
     */
    private $orderBasketHash = '';

    /**
     * @var string
     */
    private $orderState = '';

    /**
     * @var string
     */
    private $orderStateData = '';

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return void
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int|null
     */
    public function getMagentoOrderEntityId()
    {
        return $this->magentoOrderEntityId;
    }

    /**
     * @param int|null $magentoOrderEntityId
     *
     * @return void
     */
    public function setMagentoOrderEntityId($magentoOrderEntityId)
    {
        $this->magentoOrderEntityId = $magentoOrderEntityId;
    }

    /**
     * @return string|null
     */
    public function getMagentoOrderIncrementId()
    {
        return $this->orderIncrementId;
    }

    /**
     * @param string|null $orderIncrementId
     *
     * @return void
     */
    public function setMagentoOrderIncrementId($orderIncrementId)
    {
        $this->orderIncrementId = $orderIncrementId;
    }

    /**
     * @return string
     */
    public function getOrderPreCheckResult()
    {
        return $this->orderPreCheckResult;
    }

    /**
     * @param string $orderPreCheckResult
     *
     * @return void
     */
    public function setOrderPreCheckResult($orderPreCheckResult)
    {
        $this->orderPreCheckResult = $orderPreCheckResult;
    }

    /**
     * @return bool
     */
    public function getShippingReported()
    {
        return $this->shippingReported;
    }

    /**
     * @param bool $shippingReported
     *
     * @return void
     */
    public function setShippingReported($shippingReported)
    {
        $this->shippingReported = $shippingReported;
    }

    /**
     * @return string|null
     */
    public function getReportedTrackingCode()
    {
        return $this->reportedTrackingCode;
    }

    /**
     * @param string|null $reportedTrackingCode
     *
     * @return void
     */
    public function setReportedTrackingCode($reportedTrackingCode)
    {
        $this->reportedTrackingCode = $reportedTrackingCode;
    }

    /**
     * @return string
     */
    public function getOrderBasketHash()
    {
        return $this->orderBasketHash;
    }

    /**
     * @param string $orderBasketHash
     *
     * @return void
     */
    public function setOrderBasketHash($orderBasketHash)
    {
        $this->orderBasketHash = $orderBasketHash;
    }

    /**
     * @return string
     */
    public function getOrderState()
    {
        return $this->orderState;
    }

    /**
     * @param string $orderState
     *
     * @return void
     */
    public function setOrderState($orderState)
    {
        $this->orderState = $orderState;
    }

    /**
     * @return string
     */
    public function getOrderStateData()
    {
        return $this->orderStateData;
    }

    /**
     * @param string $orderStateData
     *
     * @return void
     */
    public function setOrderStateData($orderStateData)
    {
        $this->orderStateData = $orderStateData;
    }
}
