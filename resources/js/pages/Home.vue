<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/App.vue';
import type { Totals } from '@/types/totals';

const props = defineProps<{
    totals: Totals | null;
    error: string | null;
}>();

const isRefreshing = ref(false);

const number = new Intl.NumberFormat(undefined, { maximumFractionDigits: 0 });
const fiat = new Intl.NumberFormat(undefined, { style: 'currency', currency: 'USD', maximumFractionDigits: 0 });

function formatMetric(key: keyof Totals, value: number) {
    if (key === 'budget') return fiat.format(value);
    return number.format(value);
}

const cards = computed(() => {
    return [
        { key: 'budget', label: 'Budget' },
        { key: 'impressions', label: 'Impressions' },
        { key: 'clicks', label: 'Clicks' },
        { key: 'conversions', label: 'Conversions' },
        { key: 'users', label: 'Users' },
        { key: 'sessions', label: 'Sessions' },
    ] as const;
});

const showSkeleton = computed(() => {
    return isRefreshing.value || (!props.totals && !props.error);
});

function refresh() {
    router.get(
        route('home'),
        { refresh: 1 },
        {
            only: ['totals', 'error'],
            preserveScroll: true,
            preserveState: true,
            onStart: () => {
                isRefreshing.value = true;
            },
            onFinish: () => {
                isRefreshing.value = false;
            },
        },
    );
}
</script>

<template>
    <AppLayout title="Max Connect Assessment">
        <div class="mb-4 flex items-center justify-between gap-4">
            <div v-if="error" class="flex-1 rounded-lg border border-orange-200 bg-orange-50 px-4 py-3 text-sm text-amber-900">
                {{ error }}
            </div>
            <div v-else class="flex-1" />

            <button
                type="button"
                @click="refresh"
                :disabled="isRefreshing"
                class="inline-flex items-center rounded-lg bg-zinc-900 px-3 py-2 text-sm font-medium text-white hover:bg-zinc-800 disabled:cursor-not-allowed disabled:opacity-50"
            >
                <span v-if="isRefreshing">Refreshing…</span>
                <span v-else>Refresh</span>
            </button>
        </div>

        <div v-if="showSkeleton" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div v-for="i in 6" :key="i" class="rounded-lg border bg-white p-4">
                <div class="h-4 w-24 animate-pulse rounded bg-zinc-200"></div>
                <div class="mt-3 h-7 w-32 animate-pulse rounded bg-zinc-200"></div>
            </div>
        </div>

        <div v-else-if="totals" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div v-for="c in cards" :key="c.key" class="rounded-lg border bg-white p-4">
                <div class="text-sm text-zinc-500">{{ c.label }}</div>
                <div class="text-xl font-semibold">
                    {{ formatMetric(c.key, totals[c.key]) }}
                </div>
            </div>
        </div>

        <div v-else class="text-sm text-zinc-500">No data available.</div>
    </AppLayout>
</template>
