<?php

namespace Axytos\KaufAufRechnung\Api;

use Magento\Framework\App\ActionInterface;

interface PaymentControllerInterface
{
    /**
     * Payment.
     *
     * @param string $paymentId
     * @return void
     */
    public function payment(string $paymentId);
}
