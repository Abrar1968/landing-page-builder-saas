<?php

namespace App\Contracts;

use App\DTOs\CheckoutSession;
use App\DTOs\PaymentResult;
use App\Models\User;

interface PaymentGatewayInterface
{
    public function createCustomer(User $user): string;

    public function createCheckoutSession(User $user, string $priceId, string $successUrl, string $cancelUrl): CheckoutSession;

    public function createBillingPortalSession(User $user, string $returnUrl): string;

    public function cancelSubscription(string $subscriptionId): PaymentResult;

    public function resumeSubscription(string $subscriptionId): PaymentResult;

    public function handleWebhook(string $payload, string $signature): PaymentResult;

    public function getSubscription(string $subscriptionId): ?array;
}
