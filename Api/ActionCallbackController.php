<?php

namespace Axytos\KaufAufRechnung\Api;

use Axytos\ECommerce\Clients\Invoice\PluginConfigurationValidator;
use Axytos\ECommerce\Clients\ErrorReporting\ErrorReportingClientInterface;
use Axytos\KaufAufRechnung\Core\Abstractions\Model\Actions\ActionExecutorInterface;
use Axytos\KaufAufRechnung\Core\Abstractions\Model\Actions\ActionResultInterface;
use Axytos\KaufAufRechnung\Core\Model\Actions\Results\FatalErrorResult;
use Axytos\KaufAufRechnung\Core\Model\Actions\Results\InvalidDataResult;
use Axytos\KaufAufRechnung\Core\Model\Actions\Results\InvalidMethodResult;
use Axytos\KaufAufRechnung\Core\Model\Actions\Results\PluginNotConfiguredResult;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Logging\LoggerAdapterInterface;
use Exception;
use Magento\Framework\Webapi\Exception as WebapiException;
use Magento\Framework\Webapi\Request as WebapiRequest;
use Magento\Framework\Webapi\Response as WebapiResponse;

/**
 * Call: http://localhost/rest/V1/axytos/KaufAufRechnung/action
 * Or Call: http://localhost/rest/[store_code]/V1/axytos/KaufAufRechnung/action -- CURRENTLY NOT SUPPORTED
 *
 * See: https://www.mageplaza.com/devdocs/magento-2-create-api/
 *
 * @package Axytos\KaufAufRechnung\Api
 */
class ActionCallbackController implements ActionCallbackControllerInterface
{
    /**
     * @var \Magento\Framework\Webapi\Request
     */
    private $request;
    /**
     * @var \Magento\Framework\Webapi\Response
     */
    private $response;

    /**
     * @var \Axytos\KaufAufRechnung\Core\Abstractions\Model\Actions\ActionExecutorInterface
     */
    private $actionExecutor;

    /**
     * @var \Axytos\ECommerce\Clients\Invoice\PluginConfigurationValidator
     */
    private $pluginConfigurationValidator;

    /**
     * @var \Axytos\ECommerce\Clients\ErrorReporting\ErrorReportingClientInterface
     */
    private $errorReportingClient;

    /**
     * @var \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Logging\LoggerAdapterInterface
     */
    private $logger;

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
            if ($this->isNotPostRequest()) {
                $this->setResult(new InvalidMethodResult($this->getRequestMethod()));
                return;
            }

            if ($this->pluginConfigurationValidator->isInvalid()) {
                $this->setResult(new PluginNotConfiguredResult());
                return;
            }

            $this->processAction();
        } catch (WebapiException $webApiException) {
            // rethrow WebapiException
            // because mangento sets http status codes via WebapiExceptions
            throw $webApiException;
        } catch (Exception $exception) {
            $this->errorReportingClient->reportError($exception);
            $this->setResult(new FatalErrorResult());
        }
    }

    /**
     * @return void
     */
    private function processAction()
    {
        $rawBody = $this->getRequestBody();

        if ($rawBody === '') {
            $this->logger->error('Process Action Request: HTTP request body empty');
            $this->setResult(new InvalidDataResult('HTTP request body empty'));
            return;
        }

        $decodedBody = json_decode($rawBody, true);
        if (!is_array($decodedBody)) {
            $this->logger->error('Process Action Request: HTTP request body is not a json object');
            $this->setResult(new InvalidDataResult('HTTP request body is not a json object'));
            return;
        }

        $loggableRequestBody = $decodedBody;
        if (array_key_exists('clientSecret', $loggableRequestBody)) {
            $loggableRequestBody['clientSecret'] = '****';
        }
        $encodedLoggableRequestBody = json_encode($loggableRequestBody);
        $this->logger->info("Process Action Request: request body '$encodedLoggableRequestBody'");

        $clientSecret = array_key_exists('clientSecret', $decodedBody) ? $decodedBody['clientSecret'] : null;
        if (!is_string($clientSecret)) {
            $this->logger->error("Process Action Request: Required string property 'clientSecret' is missing");
            $this->setResult(new InvalidDataResult('Required string property', 'clientSecret'));
            return;
        }

        $action = array_key_exists('action', $decodedBody) ?  $decodedBody['action'] : null;
        if (!is_string($action)) {
            $this->logger->error("Process Action Request: Required string property 'action' is missing");
            $this->setResult(new InvalidDataResult('Required string property', 'action'));
            return;
        }

        $params = array_key_exists('params', $decodedBody) ? $decodedBody['params'] : null;
        if (!is_null($params) && !is_array($params)) {
            $this->logger->error("Process Action Request: Optional object property 'params' ist not an array");
            $this->setResult(new InvalidDataResult('Optional object property', 'params'));
            return;
        }

        $result = $this->actionExecutor->executeAction($clientSecret, $action, $params);
        $this->setResult($result);
    }

    /**
     * @return string
     */
    private function getRequestBody()
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
    private function getRequestMethod()
    {
        return $this->request->getMethod();
    }

    /**
     * @return bool
     */
    private function isNotPostRequest()
    {
        return $this->getRequestMethod() !== 'POST';
    }

    /**
     * @param \Axytos\KaufAufRechnung\Core\Abstractions\Model\Actions\ActionResultInterface $result
     * @return void
     */
    private function setResult($result)
    {
        $this->sendJsonResponse($result->getHttpStatusCode(), $result);
    }

    /**
     * @param int $statusCode
     * @param mixed $data
     * @return never
     */
    private function sendJsonResponse($statusCode, $data)
    {
        $this->response->clearHeaders();
        $this->response->setHttpResponseCode($statusCode);
        $this->response->setMimeType('application/json');
        $this->response->setContent(json_encode($data));
        $this->response->send();
        // phpcs:ignore Magento2.Security.LanguageConstruct.ExitUsage
        die(); // so setStatusCode will not be overwritten by magento, see: https://magento.stackexchange.com/a/283231
    }
}
