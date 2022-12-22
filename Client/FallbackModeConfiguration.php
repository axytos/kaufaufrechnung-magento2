<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\Client;

use Axytos\ECommerce\Abstractions\FallbackModeConfigurationInterface;
use Axytos\ECommerce\Abstractions\FallbackModes;
use Axytos\KaufAufRechnung\Configuration\PluginConfiguration;

class FallbackModeConfiguration implements FallbackModeConfigurationInterface
{
    /**
     * @var \Axytos\KaufAufRechnung\Configuration\PluginConfiguration
     */
    public $pluginConfig;

    public function __construct(PluginConfiguration $pluginConfig)
    {
        $this->pluginConfig = $pluginConfig;
    }

    public function getFallbackMode(): string
    {
        return FallbackModes::ALL_PAYMENT_METHODS;
    }
}
