<?php

namespace App\Actions;

use App\Services\MaxConnectService;

class GetAdMetricsAction
{
    public function __construct(private readonly MaxConnectService $client) {}

    public function handle(): array
    {
        return [];
    }
}