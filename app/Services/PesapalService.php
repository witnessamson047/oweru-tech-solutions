<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class PesapalService
{
    protected string $consumerKey;
    protected string $consumerSecret;
    protected string $baseUrl;

    public function __construct()
    {
        $this->consumerKey = (string) config('services.pesapal.consumer_key', '');
        $this->consumerSecret = (string) config('services.pesapal.consumer_secret', '');
        $this->baseUrl = (string) config('services.pesapal.base_url', 'https://pay.pesapal.com/v3');
    }

    /**
     * Get a bearer token from PesaPal (cached for 4 minutes).
     */
    public function getToken(): string
    {
        return Cache::remember('pesapal_token', 240, function () {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/api/Auth/RequestToken", [
                'consumer_key' => $this->consumerKey,
                'consumer_secret' => $this->consumerSecret,
            ]);

            $data = $response->json();

            if (!empty($data['error'])) {
                throw new \RuntimeException('PesaPal auth failed: ' . ($data['error']['message'] ?? 'Unknown error'));
            }

            return $data['token'];
        });
    }

    /**
     * Register an IPN (Instant Payment Notification) URL.
     */
    public function registerIpn(string $url, string $method = 'GET'): array
    {
        $response = $this->authenticatedRequest('POST', '/api/URLSetup/RegisterIPN', [
            'url' => $url,
            'ipn_notification_type' => $method,
        ]);

        return $response;
    }

    /**
     * Submit an order/payment request to PesaPal.
     *
     * @param array $order  Must contain: id, currency, amount, description, callback_url, notification_id, billing_address
     */
    public function submitOrder(array $order): array
    {
        $response = $this->authenticatedRequest('POST', '/api/Transactions/SubmitOrderRequest', $order);

        if (!empty($response['error'])) {
            throw new \RuntimeException('PesaPal order failed: ' . ($response['error']['message'] ?? 'Unknown error'));
        }

        return $response;
    }

    /**
     * Get the status of a transaction.
     */
    public function getTransactionStatus(string $orderTrackingId): array
    {
        $response = $this->authenticatedRequest('GET', "/api/Transactions/GetTransactionStatus?orderTrackingId={$orderTrackingId}");

        return $response;
    }

    /**
     * Make an authenticated request to the PesaPal API.
     */
    protected function authenticatedRequest(string $method, string $endpoint, array $data = []): array
    {
        $token = $this->getToken();

        $http = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response = match (strtoupper($method)) {
            'GET' => $http->get("{$this->baseUrl}{$endpoint}"),
            'POST' => $http->post("{$this->baseUrl}{$endpoint}", $data),
            default => throw new \InvalidArgumentException("Unsupported HTTP method: {$method}"),
        };

        return $response->json();
    }
}
