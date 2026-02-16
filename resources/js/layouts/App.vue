<script setup lang="ts">
defineProps<{
    title?: string;
}>();
</script>

<template>
    <div class="min-h-screen px-4 py-10">
        <div class="mx-auto max-w-5xl">
            <header v-if="title" class="mb-6">
                <h1 class="text-2xl font-semibold text-zinc-900">
                    {{ title }}
                </h1>
            </header>

            <main class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-zinc-200">
                <slot />
            </main>
        </div>
    </div>
</template>
