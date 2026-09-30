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
    return '~$' + (n < 1 ? n.toFixed(6).replace(/\.?0+$/, '') : n.toFixed(2));
}
</script>

<template>
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">

        <!-- Total Requests -->
        <div class="rounded-[12px] border border-gray-100 bg-white p-5 shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
            <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-gray-400">Total Requests</p>
            <p class="mt-2 text-[30px] font-bold leading-none text-gray-900">{{ fmt(kpi.total_requests) }}</p>
        </div>

        <!-- Input Tokens -->
        <div class="rounded-[12px] border border-gray-100 bg-white p-5 shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
            <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-gray-400">Total Input Tokens</p>
            <p class="mt-2 text-[30px] font-bold leading-none text-orange-500">{{ fmt(kpi.total_input_tokens) }}</p>
        </div>

        <!-- Output Tokens -->
        <div class="rounded-[12px] border border-gray-100 bg-white p-5 shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
            <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-gray-400">Output Tokens</p>
            <p class="mt-2 text-[30px] font-bold leading-none text-emerald-500">{{ fmt(kpi.total_output_tokens) }}</p>
        </div>

        <!-- Total Tokens -->
        <div class="rounded-[12px] border border-gray-100 bg-white p-5 shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
            <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-gray-400">Total Tokens</p>
            <p class="mt-2 text-[30px] font-bold leading-none text-blue-500">{{ fmt(kpi.total_tokens) }}</p>
        </div>

        <!-- Est. Cost -->
        <div class="rounded-[12px] border border-gray-100 bg-white p-5 shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
            <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-gray-400">Est. Cost</p>
            <p class="mt-2 text-[30px] font-bold leading-none text-orange-500">{{ fmtCost(kpi.est_cost) }}</p>
            <p class="mt-1.5 text-[11px] text-gray-400">Estimated, not actual billing.</p>
        </div>

    </div>
</template>
