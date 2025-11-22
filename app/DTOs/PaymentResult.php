<?php

namespace App\DTOs;

class PaymentResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $transactionId = null,
        public readonly ?string $message = null,
        public readonly ?array $metadata = null,
    ) {}

    public static function success(string $transactionId, ?string $message = null, ?array $metadata = null): self
    {
        return new self(
            success: true,
            transactionId: $transactionId,
            message: $message,
            metadata: $metadata,
        );
    }

    public static function failure(string $message, ?array $metadata = null): self
    {
        return new self(
            success: false,
            message: $message,
            metadata: $metadata,
        );
    }
}
