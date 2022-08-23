<?php declare(strict_types=1);

namespace Axytos\KaufAufRechnung\Client;

use Axytos\ECommerce\Abstractions\UserAgentInfoProviderInterface;
use Axytos\ECommerce\PackageInfo\ComposerPackageInfoProvider;
use Magento\Framework\App\ProductMetadataInterface;

class UserAgentInfoProvider implements UserAgentInfoProviderInterface
{
    private ProductMetadataInterface $productMetaDataInterface;
    private ComposerPackageInfoProvider $composerPackageInfoProvider;
    
    public function __construct(ProductMetadataInterface $productMetaDataInterface, ComposerPackageInfoProvider $composerPackageInfoProvider)
    {
        $this->productMetaDataInterface = $productMetaDataInterface;
        $this->composerPackageInfoProvider = $composerPackageInfoProvider;
    }

    public function getPluginName(): string
    {
        return "KaufAufRechnung";
    }

    public function getPluginVersion(): string
    {
        $packageName = 'axytos/kaufaufrechnung-magento2';
        
        if (!$this->composerPackageInfoProvider->isInstalled($packageName))
        {
            return '0.0.0';
        }

        /** @phpstan-ignore-next-line */
        return $this->composerPackageInfoProvider->getVersion($packageName);
    }
    
    public function getShopSystemName(): string
    {
        return "Magento";
    }
    
    public function getShopSystemVersion(): string
    {
        return $this->productMetaDataInterface->getVersion();
    }
}
