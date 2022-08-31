<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\Client;

use Axytos\ECommerce\Abstractions\ApiHostProviderInterface;
use Axytos\KaufAufRechnung\Configuration\PluginConfiguration;

class ApiHostProvider implements ApiHostProviderInterface
{
    public PluginConfiguration $pluginConfig;

    public function __construct(PluginConfiguration $pluginConfig)
    {
        $this->pluginConfig = $pluginConfig;
    }

    public function getApiHost(): string
    {
        return $this->pluginConfig->getApiHost();
    }
}
