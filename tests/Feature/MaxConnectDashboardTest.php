<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Cache::flush();
    Http::preventStrayRequests();

    config([
        'services.max_connect.base_url' => 'https://example.test',
        'services.max_connect.api_token' => 'test-token',
    ]);
});

function campaignPayload(array $campaigns): array
{
    return [
        'data' => [
            'campaigns' => $campaigns,
        ],
    ];
}

it('renders dashboard with aggregated totals on GET', function () {
    Http::fake([
        '*' => Http::response(
            campaignPayload([
                [
                    'id' => '1',
                    'budget' => 1000,
                    'impressions' => 5000,
                    'clicks' => 250,
                    'conversions' => 25,
                    'users' => 200,
                    'sessions' => 300,
                ],
                [
                    'id' => '2',
                    'budget' => 2000,
                    'impressions' => 10000,
                    'clicks' => 500,
                    'conversions' => 50,
                    'users' => 400,
                    'sessions' => 600,
                ],
            ]),
            200
        ),
    ]);

    $response = $this->get(route('home'));

    $response->assertStatus(200);

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Home')
        ->where('error', null)
        ->where('totals', [
            'budget' => 3000,
            'impressions' => 15000,
            'clicks' => 750,
            'conversions' => 75,
            'users' => 600,
            'sessions' => 900,
        ])
    );

    Http::assertSentCount(1);
});

it('uses cache on subsequent GET requests', function () {
    Http::fake([
        '*' => Http::response(
            campaignPayload([
                [
                    'id' => '1',
                    'budget' => 1000,
                    'impressions' => 5000,
                    'clicks' => 250,
                    'conversions' => 25,
                    'users' => 200,
                    'sessions' => 300,
                ],
            ]),
            200
        ),
    ]);

    // First request hits API and caches
    $this->get(route('home'))->assertStatus(200);
    Http::assertSentCount(1);

    // Second request should be served from cache
    $this->get(route('home'))->assertStatus(200);
    Http::assertSentCount(1);
});

it('POST refresh busts cache and calls API again', function () {
    Http::fake([
        '*' => Http::response(
            campaignPayload([
                [
                    'id' => '1',
                    'budget' => 1000,
                    'impressions' => 5000,
                    'clicks' => 250,
                    'conversions' => 25,
                    'users' => 200,
                    'sessions' => 300,
                ],
            ]),
            200
        ),
    ]);

    // Initial GET caches
    $this->get(route('home'))->assertStatus(200);
    Http::assertSentCount(1);

    // POST refresh should clear cache and refetch
    $this->post(route('home'))->assertStatus(200);
    Http::assertSentCount(2);
});

it('returns an error when API fails and no cache exists', function () {
    Http::fake([
        '*' => Http::response(['error' => true], 500),
    ]);

    $response = $this->get(route('home'));
    $response->assertStatus(200);

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Home')
        ->where('totals', null)
        ->has('error')
        ->where('error', fn ($value) => is_string($value) && str_contains($value, 'Unable to fetch'))
    );

    expect(Http::recorded())->not->toBeEmpty();
});

it('serves cached totals on GET even if the API is failing', function () {
    Http::fake([
        '*' => Http::response(campaignPayload([
            [
                'id' => '1',
                'budget' => 1000,
                'impressions' => 5000,
                'clicks' => 250,
                'conversions' => 25,
                'users' => 200,
                'sessions' => 300,
            ],
        ]), 200),
    ]);

    $this->get(route('home'))->assertStatus(200);
    Http::assertSentCount(1);

    // Fake API is down
    Http::fake([
        '*' => Http::response(['error' => true], 500),
    ]);

    // Cache::remember returns immediately and does NOT call the API
    $response = $this->get(route('home'));
    $response->assertStatus(200);

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Home')
        ->where('totals', [
            'budget' => 1000,
            'impressions' => 5000,
            'clicks' => 250,
            'conversions' => 25,
            'users' => 200,
            'sessions' => 300,
        ])
        ->where('error', null)
    );

    Http::assertSentCount(0);
});

it('returns error when campaigns key is missing', function () {
    Http::fake([
        '*' => Http::response(['data' => []], 200),
    ]);

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->where('totals', null)
            ->where('error', 'Unexpected API response: missing campaigns.')
        );
});