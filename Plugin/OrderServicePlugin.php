<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\Plugin;

use Axytos\ECommerce\Clients\ErrorReporting\ErrorReportingClientInterface;
use Axytos\ECommerce\Clients\Invoice\PluginConfigurationValidator;
use Axytos\ECommerce\Clients\Invoice\ShopActions;
use Axytos\KaufAufRechnung\Adapter\PluginOrderFactory;
use Axytos\KaufAufRechnung\Configuration\PluginConfiguration;
use Axytos\KaufAufRechnung\Core\Model\AxytosOrderFactory;
use Axytos\KaufAufRechnung\Core\OrderStateMachine;
use Axytos\KaufAufRechnung\Exception\DisablePaymentMethodException;
use Axytos\KaufAufRechnung\Model\Constants;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Service\OrderService;

class OrderServicePlugin
{
    /**
     * @var PluginConfigurationValidator
     */
    private $pluginConfigurationValidator;
    /**
     * @var OrderStateMachine
     */
    private $orderStateMachine;
    /**
     * @var ErrorReportingClientInterface
     */
    private $errorReportingClient;
    /**
     * @var PluginConfiguration
     */
    private $pluginConfiguration;

    /**
     * @var PluginOrderFactory
     */
    private $pluginOrderFactory;

    /**
     * @var AxytosOrderFactory
     */
    private $axytosOrderFactory;

    public function __construct(
        PluginConfigurationValidator $pluginConfigurationValidator,
        OrderStateMachine $orderStateMachine,
        ErrorReportingClientInterface $errorReportingClient,
        PluginConfiguration $pluginConfiguration,
        PluginOrderFactory $pluginOrderFactory,
        AxytosOrderFactory $axytosOrderFactory
    ) {
        $this->pluginConfigurationValidator = $pluginConfigurationValidator;
        $this->orderStateMachine = $orderStateMachine;
        $this->errorReportingClient = $errorReportingClient;
        $this->pluginConfiguration = $pluginConfiguration;
        $this->pluginOrderFactory = $pluginOrderFactory;
        $this->axytosOrderFactory = $axytosOrderFactory;
    }

    public function afterPlace(OrderService $subject, OrderInterface $order): OrderInterface
    {
        try {
            if (is_null($order->getPayment()) || Constants::PAYMENT_METHOD_CODE !== $order->getPayment()->getMethod()) {
                return $order;
            }

            if ($this->pluginConfigurationValidator->isInvalid()) {
                return $order;
            }

            // order is already persisted by magento
            // so PluginOrderFactory can load complete sales model
            $pluginOrder = $this->pluginOrderFactory->create($order);
            $axytosOrder = $this->axytosOrderFactory->create($pluginOrder);
            $axytosOrder->checkout();

            $shopAction = $axytosOrder->getOrderCheckoutAction();

            if (ShopActions::CHANGE_PAYMENT_METHOD === $shopAction) {
                $this->orderStateMachine->setCanceled($order);

                $errorMessage = $this->pluginConfiguration->getCustomErrorMessage();
                if (is_null($errorMessage)) {
                    $errorPhrase = __('PAYMENT_REJECTED_MESSAGE');
                } else {
                    $errorPhrase = new Phrase($errorMessage);
                }

                throw new DisablePaymentMethodException($errorPhrase, Constants::PAYMENT_METHOD_CODE);
            }

            $this->orderStateMachine->setConfiguredAfterCheckoutOrderStatus($order);

            return $order;
        } catch (DisablePaymentMethodException $exception) {
            $this->orderStateMachine->setRejected($order);
            $couldNotSaveException = new CouldNotSaveException(__($exception->getMessage()));
            $couldNotSaveException->addException($exception);
            throw $couldNotSaveException;
        } catch (LocalizedException $exception) {
            $this->orderStateMachine->setTechnicalError($order);
            $couldNotSaveException = new CouldNotSaveException(__($exception->getMessage()));
            $couldNotSaveException->addException($exception);
            throw $couldNotSaveException;
        } catch (\Exception $e) {
            $this->orderStateMachine->setTechnicalError($order);
            $this->errorReportingClient->reportError($e);
            throw $e;
        }
    }
}
