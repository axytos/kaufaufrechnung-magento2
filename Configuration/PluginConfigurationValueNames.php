<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\Configuration;

abstract class PluginConfigurationValueNames
{
    public const API_KEY = 'payment/axytos_kauf_auf_rechnung/api_key';
    public const API_HOST = 'payment/axytos_kauf_auf_rechnung/api_host';
    public const CLIENT_SECRET = 'payment/axytos_kauf_auf_rechnung/client_secret';
    public const ORDER_STATUS_AFTER_CHECKOUT = 'payment/axytos_kauf_auf_rechnung/order_status_after_checkout';
    public const ERROR_MESSAGE = 'payment/axytos_kauf_auf_rechnung/error_message';
    public const MAXIMUM_ORDER_AMOUNT = 'payment/axytos_kauf_auf_rechnung/maximum_order_amount';
}
