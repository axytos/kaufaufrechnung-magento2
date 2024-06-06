<?php

namespace Axytos\KaufAufRechnung\Model\Data;

interface AxytosOrderAttributesInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const ID = 'id';
    const MAGENTO_ORDER_ENTITY_ID = 'magento_order_entity_id';
    const MAGENTO_ORDER_INCREMENT_ID = 'magento_order_increment_id';
    const ORDER_PRE_CHECK_RESULT = 'order_pre_check_result';
    const SHIPPING_REPORTED = 'shipping_reported';
    const REPORTED_TRACKING_CODE = 'reported_tracking_code';
    const ORDER_BASKET_HASH = 'order_basket_hash';
    const ORDER_STATE = 'order_state';
    const ORDER_STATE_DATA = 'order_state_data';

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return void
     */
    public function setId($id);

    /**
     * @return int|null
     */
    public function getMagentoOrderEntityId();

    /**
     * @param int|null  $magentoOrderEntityId
     * @return void
     */
    public function setMagentoOrderEntityId($magentoOrderEntityId);

    /**
     * @return string|null
     */
    public function getMagentoOrderIncrementId();

    /**
     * @param string|null $magentoOrderIncrementId
     * @return void
     */
    public function setMagentoOrderIncrementId($magentoOrderIncrementId);

    /**
     * @return string
     */
    public function getOrderPreCheckResult();

    /**
     * @param string $orderPreCheckResult
     * @return void
     */
    public function setOrderPreCheckResult($orderPreCheckResult);

    /**
     * @return bool
     */
    public function getShippingReported();

    /**
     * @param bool $shippingReported
     * @return void
     */
    public function setShippingReported($shippingReported);

    /**
     * @return string|null
     */
    public function getReportedTrackingCode();

    /**
     * @param string|null $reportedTrackingCode
     * @return void
     */
    public function setReportedTrackingCode($reportedTrackingCode);

    /**
     * @return string
     */
    public function getOrderBasketHash();

    /**
     * @param string $orderBasketHash
     * @return void
     */
    public function setOrderBasketHash($orderBasketHash);

    /**
     * @return string
     */
    public function getOrderState();

    /**
     * @param string $orderState
     * @return void
     */
    public function setOrderState($orderState);

    /**
     * @return string
     */
    public function getOrderStateData();

    /**
     * @param string $orderStateData
     * @return void
     */
    public function setOrderStateData($orderStateData);
}
