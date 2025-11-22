<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Services\Payment\Gateways\StripeGateway;

class PaymentContext
{
    protected PaymentGatewayInterface $gateway;

    public function __construct(?string $gateway = null)
    {
        $this->gateway = match ($gateway ?? config('services.payment.default', 'stripe')) {
            'stripe' => new StripeGateway(),
            default => new StripeGateway(),
        };
    }

    public function gateway(): PaymentGatewayInterface
    {
        return $this->gateway;
    }

    public function __call(string $method, array $arguments)
    {
        return $this->gateway->$method(...$arguments);
    }
}
