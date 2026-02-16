<script setup lang="ts">
import logo from '../../images/MaxConnectLogo.jpeg';

defineProps<{
    title?: string;
}>();
</script>

<template>
    <Head :title="title" />

    <div class="min-h-screen bg-[#FDFDFC] text-[#1b1b18]">
        <header class="border-b bg-white">
            <div class="mx-auto flex max-w-6xl items-center gap-3 px-6 py-4">
                <img :src="logo" alt="Max Connect" class="h-8 w-auto" />

                <h1 class="text-lg font-semibold">
                    {{ title }}
                </h1>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-6 py-6">
            <slot />
        </main>
    </div>
</template>
