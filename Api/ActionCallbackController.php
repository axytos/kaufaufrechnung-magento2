<?php

namespace Axytos\KaufAufRechnung\Api;

use Axytos\ECommerce\Clients\ErrorReporting\ErrorReportingClientInterface;
use Axytos\ECommerce\Clients\Invoice\PluginConfigurationValidator;
use Axytos\KaufAufRechnung\Core\Abstractions\Model\Actions\ActionExecutorInterface;
use Axytos\KaufAufRechnung\Core\AxytosActionControllerTrait;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Logging\LoggerAdapterInterface;
use Magento\Framework\Webapi\Exception as WebapiException;
use Magento\Framework\Webapi\Request as WebapiRequest;
use Magento\Framework\Webapi\Response as WebapiResponse;

/**
 * Call: http://localhost/rest/V1/axytos/KaufAufRechnung/action
 * Or Call: http://localhost/rest/[store_code]/V1/axytos/KaufAufRechnung/action -- CURRENTLY NOT SUPPORTED.
 *
 * See: https://www.mageplaza.com/devdocs/magento-2-create-api/
 */
class ActionCallbackController implements ActionCallbackControllerInterface
{
    use AxytosActionControllerTrait;

    /**
     * @var WebapiRequest
     */
    private $request;
    /**
     * @var WebapiResponse
     */
    private $response;

    /**
     * @var ErrorReportingClientInterface
     */
    private $errorReportingClient;

    public function __construct(
        WebapiRequest $request,
        WebapiResponse $response,
        ActionExecutorInterface $actionExecutor,
        PluginConfigurationValidator $pluginConfigurationValidator,
        ErrorReportingClientInterface $errorReportingClient,
        LoggerAdapterInterface $logger
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->actionExecutor = $actionExecutor;
        $this->pluginConfigurationValidator = $pluginConfigurationValidator;
        $this->errorReportingClient = $errorReportingClient;
        $this->logger = $logger;
    }

    /**
     * @return void
     */
    public function execute()
    {
        try {
            $this->executeActionInternal();
        } catch (WebapiException $webApiException) {
            // rethrow WebapiException
            // because mangento sets http status codes via WebapiExceptions
            throw $webApiException;
        } catch (\Exception $exception) {
            $this->errorReportingClient->reportError($exception);
            $this->setErrorResult();
        }
    }

    /**
     * @return string
     */
    protected function getRequestBody()
    {
        $content = $this->request->getContent();
        if (!is_string($content)) {
            return '';
        }

        return $content;
    }

    /**
     * @return string
     */
    protected function getRequestMethod()
    {
        return $this->request->getMethod();
    }

    /**
     * @param string $responseBody
     * @param int    $statusCode
     *
     * @return void
     */
    protected function setResponseBody($responseBody, $statusCode)
    {
        $this->sendJsonResponse($statusCode, $responseBody);
    }

    /**
     * @param int    $statusCode
     * @param string $data
     *
     * @return never
     */
    private function sendJsonResponse($statusCode, $data)
    {
        $this->response->clearHeaders();
        $this->response->setHttpResponseCode($statusCode);
        $this->response->setMimeType('application/json');
        $this->response->setContent($data);
        $this->response->send();
        // phpcs:ignore Magento2.Security.LanguageConstruct.ExitUsage
        exit; // so setStatusCode will not be overwritten by magento, see: https://magento.stackexchange.com/a/283231
    }
}
