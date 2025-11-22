<?php

namespace App\DTOs;

class CheckoutSession
{
    public function __construct(
        public readonly string $id,
        public readonly string $url,
        public readonly string $status,
        public readonly ?string $customerId = null,
        public readonly ?string $subscriptionId = null,
    ) {}

    public static function fromStripe(object $session): self
    {
        return new self(
            id: $session->id,
            url: $session->url,
            status: $session->status,
            customerId: $session->customer,
            subscriptionId: $session->subscription,
        );
    }
}
