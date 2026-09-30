<script setup>
defineProps({
    kpi: { type: Object, default: () => ({}) },
});

function fmt(n) {
    return Number(n ?? 0).toLocaleString();
}

function fmtCost(c) {
    const n = Number(c ?? 0);
    if (n === 0) return '$0.00';
    return n < 1 ? '$' + n.toFixed(6).replace(/\.?0+$/, '') : '$' + n.toFixed(2);
}
</script>

<template>
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
        <!-- Total Requests -->
        <div class="rounded-xl border border-gray-700 bg-gray-800/60 p-4 backdrop-blur">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Requests</p>
            <p class="mt-2 text-2xl font-bold text-white">{{ fmt(kpi.total_requests) }}</p>
        </div>
        <!-- Input Tokens -->
        <div class="rounded-xl border border-gray-700 bg-gray-800/60 p-4 backdrop-blur">
            <p class="text-xs font-semibold uppercase tracking-widest text-orange-400">Input Tokens</p>
            <p class="mt-2 text-2xl font-bold text-white">{{ fmt(kpi.total_input_tokens) }}</p>
        </div>
        <!-- Output Tokens -->
        <div class="rounded-xl border border-gray-700 bg-gray-800/60 p-4 backdrop-blur">
            <p class="text-xs font-semibold uppercase tracking-widest text-teal-400">Output Tokens</p>
            <p class="mt-2 text-2xl font-bold text-white">{{ fmt(kpi.total_output_tokens) }}</p>
        </div>
        <!-- Total Tokens -->
        <div class="rounded-xl border border-gray-700 bg-gray-800/60 p-4 backdrop-blur">
            <p class="text-xs font-semibold uppercase tracking-widest text-indigo-400">Total Tokens</p>
            <p class="mt-2 text-2xl font-bold text-white">{{ fmt(kpi.total_tokens) }}</p>
        </div>
        <!-- Est. Cost -->
        <div class="rounded-xl border border-gray-700 bg-gray-800/60 p-4 backdrop-blur">
            <p class="text-xs font-semibold uppercase tracking-widest text-yellow-400">Est. Cost</p>
            <p class="mt-2 text-2xl font-bold text-white">{{ fmtCost(kpi.est_cost) }}</p>
            <p class="mt-1 text-xs text-gray-500">Manually recorded</p>
        </div>
    </div>
</template>
