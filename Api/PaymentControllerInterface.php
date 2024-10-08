<?php

namespace Axytos\KaufAufRechnung\Api;

interface PaymentControllerInterface
{
    /**
     * Payment.
     *
     * @return void
     */
    public function payment(string $paymentId);
}
