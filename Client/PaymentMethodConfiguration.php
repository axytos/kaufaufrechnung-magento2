<?php declare(strict_types=1);

namespace Axytos\KaufAufRechnung\Client;

use Axytos\ECommerce\Abstractions\PaymentMethodConfigurationInterface;
use Axytos\KaufAufRechnung\Configuration\PluginConfiguration;

class PaymentMethodConfiguration implements PaymentMethodConfigurationInterface
{
    public PluginConfiguration $pluginConfig;

    public function __construct(PluginConfiguration $pluginConfig)
    {
        $this->pluginConfig = $pluginConfig;
    }

    public function isIgnored(string $paymentMethodId): bool
    {
        return false;
    }

    public function isSafe(string $paymentMethodId): bool
    {
        return false;
    }

    public function isUnsafe(string $paymentMethodId): bool
    {
        return false;
    }

    public function isNotConfigured(string $paymentMethodId): bool
    {
        return true;
    }
}