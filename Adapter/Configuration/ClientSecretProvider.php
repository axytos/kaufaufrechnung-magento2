<?php

namespace Axytos\KaufAufRechnung\Adapter\Configuration;

use Axytos\KaufAufRechnung\Configuration\PluginConfiguration;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Configuration\ClientSecretProviderInterface;

class ClientSecretProvider implements ClientSecretProviderInterface
{
    /**
     * @var \Axytos\KaufAufRechnung\Configuration\PluginConfiguration
     */
    private $pluginConfiguration;

    /**
     * @param PluginConfiguration $pluginConfiguration
     * @return void
     */
    public function __construct(PluginConfiguration $pluginConfiguration)
    {
        $this->pluginConfiguration = $pluginConfiguration;
    }

    /**
     * @return string|null
     */
    public function getClientSecret()
    {
        return $this->pluginConfiguration->getClientSecret();
    }
}
