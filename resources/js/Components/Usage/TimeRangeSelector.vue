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
    <div
        class="flex rounded-full p-[3px]"
        style="background: rgba(0,0,0,0.05)"
        role="group"
        aria-label="Time range"
    >
        <button
            v-for="r in ranges"
            :key="r.value"
            type="button"
            :class="[
                'rounded-full px-[14px] py-[5px] text-[13px] font-medium transition-all focus:outline-none focus:ring-2 focus:ring-orange-400/40',
                activeRange === r.value
                    ? 'bg-orange-500 text-white shadow-sm'
                    : 'text-gray-500 hover:text-gray-800',
            ]"
            @click="select(r.value)"
        >
            {{ r.label }}
        </button>
    </div>
</template>
