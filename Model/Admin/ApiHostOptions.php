<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\Model\Admin;

use Axytos\ECommerce\Abstractions\ApiHostProviderInterface;
use Magento\Framework\Option\ArrayInterface;

class ApiHostOptions implements ArrayInterface
{
    /**
     * @return array<array<mixed>>
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => 'Live',
                'value' => ApiHostProviderInterface::LIVE,
            ],
            [
                'label' => 'Sandbox',
                'value' => ApiHostProviderInterface::SANDBOX,
            ],
        ];
    }
}
