<?php

namespace App\Services;

use RuntimeException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

class MaxConnectService
{
    private string $baseUrl;
    private ?string $token;

    public function __construct()
    {
        $this->baseUrl = config('services.max_connect.base_url');
        $this->token = config('services.max_connect.api_token');
    }

    /**
     * Fetch ad metrics from Max Connect API
     * 
     * @throws RuntimeException
     */
    public function fetchAdMetrics(): array
    {
        if (!$this->token) {
            throw new RuntimeException('Max Connect API token is not configured.');
        }

        try {

            $url = "{$this->baseUrl}/ad-data";

            $response = Http::withToken($this->token)
                ->timeout(5)
                ->connectTimeout(2)
                ->retry(2, 200, function ($exception) {
                    return $exception instanceof ConnectionException
                        || ($exception instanceof RequestException && $exception->response?->serverError());
                })
                ->get($url);

            if (!$response->successful()) {
                throw new RuntimeException("Max Connect API failed ({$response->status()}).");
            }

            if (!is_array($response->json())) {
                throw new RuntimeException('Max Connect returned invalid JSON.');
            }

            return $response->json();

        } catch (\Throwable $e) {
             Log::warning('MaxConnectService error', [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ]);

            throw new RuntimeException('Unable to fetch ad metrics right now.', 0, $e);
        }
    }
}