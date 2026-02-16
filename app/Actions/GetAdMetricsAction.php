<?php

namespace App\Actions;

use App\Services\MaxConnectService;
use RuntimeException;

class GetAdMetricsAction
{
    public function __construct(private readonly MaxConnectService $client) {}

    public function handle(): array
    {
        try {

            $payload = $this->client->fetchAdMetrics();

            dd($payload);

            return [
                'totals' => null,
                'error' => null,
            ];
        } catch (RuntimeException $e) {

            return [
                'totals' => null,
                'error' => $e->getMessage(),
            ];
        }
    }
}