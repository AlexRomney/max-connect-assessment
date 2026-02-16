<?php

namespace App\Actions;

use App\Services\MaxConnectService;
use RuntimeException;
use Illuminate\Support\Facades\Cache;

class GetAdMetricsAction
{
    private const METRICS = [
        'budget', 'impressions', 'clicks', 'conversions', 'users', 'sessions',
    ];

    private const CACHE_KEY = 'maxconnect:totals';
    private const CACHE_TTL_SECONDS = 60;

    public function __construct(private readonly MaxConnectService $client) {}

    public function handle(bool $refresh = false): array
    {
        try {

            if ($refresh) {
                Cache::forget(self::CACHE_KEY);
            }

            $results = Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, function () {
                $payload = $this->client->fetchAdMetrics();

                $campaigns = $payload['data']['campaigns'] ?? null;

                if (!is_array($campaigns)) {
                    throw new RuntimeException('Unexpected API response: missing campaigns.');
                }

                return [
                    'totals' => $this->aggregate($campaigns),
                    'campaignCount' => count($campaigns),
                ];
            });

            return [
                'totals' => $results['totals'],
                'error' => null,
                'campaignCount' => $results['campaignCount'],
            ];
        } catch (RuntimeException $e) {

            $cached = Cache::get(self::CACHE_KEY);

            if (is_array($cached)) {
                return [
                    'totals' => $cached['totals'],
                    'error' => 'API is currently unavailable. Showing cached results.',
                    'campaignCount' => $cached['campaignCount'],
                ];
            }

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
                $totals[$metric] += (int) ($campaign[$metric] ?? 0);
            }
        }

        return $totals;
    }
}