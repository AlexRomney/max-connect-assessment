<?php

namespace App\Actions;

use App\Services\MaxConnectService;
use RuntimeException;

class GetAdMetricsAction
{
    private const METRICS = [
        'budget',
        'impressions',
        'clicks',
        'conversions',
        'users',
        'sessions',
    ];

    public function __construct(private readonly MaxConnectService $client) {}

    public function handle(): array
    {
        try {

            $payload = $this->client->fetchAdMetrics();

            $campaigns = $payload['data']['campaigns'] ?? null;

            if (!is_array($campaigns)) {
                throw new RuntimeException('Unexpected API response: missing campaigns.');
            }
    
            $totals = $this->aggregate($campaigns);

            return [
                'totals' => $totals,
                'error' => null,
            ];
        } catch (RuntimeException $e) {

            return [
                'totals' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    private function aggregate(array $campaigns): array
    {
        $totals = array_fill_keys(self::METRICS, 0);

        foreach ($campaigns as $campaign) {
            foreach (self::METRICS as $metric) {
                $totals[$metric] += (int) $campaign[$metric] ?? 0;
            }
        }

        return $totals;
    }
}