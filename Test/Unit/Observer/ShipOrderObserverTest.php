<?php declare(strict_types=1);

namespace Axytos\KaufAufRechnung\Tests\Observer;

use Axytos\ECommerce\Clients\ErrorReporting\ErrorReportingClientInterface;
use Axytos\ECommerce\Clients\Invoice\InvoiceClientInterface;
use Axytos\ECommerce\Clients\Invoice\InvoiceOrderContextInterface;
use Axytos\ECommerce\Clients\Invoice\PluginConfigurationValidator;
use Axytos\KaufAufRechnung\Core\InvoiceOrderContextFactory;
use Axytos\KaufAufRechnung\Model\Constants;
use Axytos\KaufAufRechnung\Observer\ShipOrderObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Event\Observer;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Shipment;

class ShipOrderObserverTest extends TestCase
{
    /** @var InvoiceClientInterface&MockObject */
    private InvoiceClientInterface $invoiceClient;

    /** @var InvoiceOrderContextFactory&MockObject */
    private InvoiceOrderContextFactory $invoiceOrderContextFactory;

    /** @var PluginConfigurationValidator&MockObject */
    private PluginConfigurationValidator $pluginConfigurationValidator;

    /** @var InvoiceOrderContextInterface&MockObject */
    private InvoiceOrderContextInterface $invoiceOrderContext;

    /** @var ErrorReportingClientInterface&MockObject */
    private ErrorReportingClientInterface $errorReportingClient;

    /** @var Order&MockObject */
    private Order $order;

    private ShipOrderObserver $sut;

    public function setUp(): void
    {
        $this->invoiceClient = $this->createMock(InvoiceClientInterface::class);
        $this->order = $this->createMock(Order::class);

        $this->invoiceOrderContextFactory = $this->createMock(InvoiceOrderContextFactory::class);
        $this->pluginConfigurationValidator = $this->createMock(PluginConfigurationValidator::class);
        $this->invoiceOrderContext = $this->createMock(InvoiceOrderContextInterface::class);
        $this->errorReportingClient = $this->createMock(ErrorReportingClientInterface::class);

        $this->invoiceOrderContextFactory
            ->method('getInvoiceOrderContext')
            ->with($this->order)
            ->willReturn($this->invoiceOrderContext);

        $this->sut = new ShipOrderObserver(
            $this->invoiceClient,
            $this->invoiceOrderContextFactory,
            $this->pluginConfigurationValidator,
            $this->errorReportingClient
        );
    }

    public function test_does_not_call_invoice_client_when_plugin_config_invalid(): void
    {
        $observer = $this->createObserver(Constants::PAYMENT_METHOD_CODE);
        
        $this->pluginConfigurationValidator
            ->method('isInvalid')
            ->willReturn(true);
        
        $this->invoiceClient
            ->expects($this->never())
            ->method('reportShipping');

        $this->sut->execute($observer);
    }

    public function test_does_not_call_invoice_client_when_not_kauf_auf_rechnung(): void
    {
        $observer = $this->createObserver("not_axytos_kauf_auf_rechnung");

        $this->pluginConfigurationValidator
            ->method('isInvalid')
            ->willReturn(false);
        
        $this->invoiceClient
            ->expects($this->never())
            ->method('reportShipping');

        $this->sut->execute($observer);
    }

    public function test_does_call_invoice_client_when_kauf_auf_rechnung(): void
    {
        $observer = $this->createObserver(Constants::PAYMENT_METHOD_CODE);

        $this->pluginConfigurationValidator
            ->method('isInvalid')
            ->willReturn(false);
        
        $this->invoiceClient
            ->expects($this->once())
            ->method('reportShipping')
            ->with($this->invoiceOrderContext);

        $this->sut->execute($observer);
    }


    private function createObserver(string $paymentMethod): Observer
    {
        /** @var Shipment&MockObject */
        $shipment = $this->createMock(Shipment::class);
        /** @var Order&MockObject $order */
        $order = $this->createMock(Order::class);
        /** @var OrderPaymentInterface&MockObject $payment */
        $payment = $this->createMock(OrderPaymentInterface::class);

        $payment
            ->method("getMethod")
            ->willReturn($paymentMethod);

        $order
            ->method("getPayment")
            ->willReturn($payment);

        $shipment
            ->method("getOrder")
            ->willReturn($order);

        $observer = new Observer(["shipment" => $shipment]);

        return $observer;
    }

}