<?php

declare(strict_types=1);

namespace Axytos\KaufAufRechnung\Exception;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class DisablePaymentMethodException extends LocalizedException
{
    /**
     * @var string
     */
    private $paymentMethod;

    public function __construct(Phrase $phrase, string $paymentMethod)
    {
        parent::__construct($phrase);
        $this->paymentMethod = $paymentMethod;
    }

    /**
     * @return array<string,string>
     */
    public function getParameters()
    {
        if ('' !== $this->paymentMethod) {
            return ['paymentMethod' => $this->paymentMethod];
        }

        return [];
    }
}
