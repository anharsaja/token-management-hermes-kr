<script setup>
defineProps({
    title:    { type: String, required: true },
    value:    { type: [String, Number], required: true },
    subtitle: { type: String, default: '' },
    icon:     { type: String, default: '' },
});
</script>

<template>
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <p class="text-sm font-medium text-gray-500">{{ title }}</p>
        <p class="mt-2 text-3xl font-semibold text-gray-900">{{ value }}</p>
        <p v-if="subtitle" class="mt-1 text-sm text-gray-500">{{ subtitle }}</p>
    </div>
</template>
