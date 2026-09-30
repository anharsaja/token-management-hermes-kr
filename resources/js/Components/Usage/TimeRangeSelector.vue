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
    <!-- Pill container: rgba(255,255,255,0.05) bg, radius-full, 3px padding -->
    <div
        class="flex rounded-full p-[3px]"
        style="background: rgba(255,255,255,0.05)"
        role="group"
        aria-label="Time range"
    >
        <button
            v-for="r in ranges"
            :key="r.value"
            type="button"
            :class="[
                'rounded-full px-[14px] py-[5px] text-[13px] font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-[#F97316]/40',
                activeRange === r.value
                    ? 'bg-[#F97316] text-white font-semibold'
                    : 'text-[#94A3B8] hover:text-[#F1F5F9]',
            ]"
            @click="select(r.value)"
        >
            {{ r.label }}
        </button>
    </div>
</template>
