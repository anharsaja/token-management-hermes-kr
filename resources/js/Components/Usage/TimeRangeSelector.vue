<script setup>
import { router } from '@inertiajs/vue3';

const props = defineProps({
    ranges:      { type: Array,  default: () => [] },
    activeRange: { type: String, default: '7d' },
    activeTab:   { type: String, default: 'overview' },
});

function select(r) {
    router.get(route('usage.index'), { range: r, tab: props.activeTab }, { preserveState: false });
}
</script>

<template>
    <div class="flex gap-1 rounded-lg bg-gray-800 p-1" role="group" aria-label="Time range">
        <button
            v-for="r in ranges"
            :key="r.value"
            type="button"
            :class="[
                'rounded-md px-3 py-1 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-orange-400',
                activeRange === r.value
                    ? 'bg-orange-500 text-white'
                    : 'text-gray-400 hover:text-white',
            ]"
            @click="select(r.value)"
        >
            {{ r.label }}
        </button>
    </div>
</template>
