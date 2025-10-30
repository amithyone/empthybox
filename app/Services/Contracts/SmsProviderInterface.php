<?php

namespace App\Services\Contracts;

interface SmsProviderInterface
{
    /**
     * Get account balance
     */
    public function getBalance(): array;

    /**
     * Get available services/countries
     */
    public function getServices(): array;

    /**
     * Request a phone number for SMS reception
     */
    public function requestNumber(string $service, string $country = null, array $options = []): array;

    /**
     * Check for received SMS messages
     */
    public function getMessages(string $orderId): array;

    /**
     * Get provider name
     */
    public function getName(): string;

    /**
     * Validate API connection
     */
    public function validateConnection(): bool;

    /**
     * Retrieve pricing list with optional filters (country, service, pool, max_price)
     */
    public function getPricing(array $params = []): array;
}

