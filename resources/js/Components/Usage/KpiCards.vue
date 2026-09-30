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
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 sm:gap-4">

        <!-- Total Requests -->
        <div class="animate-fade-up animate-fade-up-1 flex flex-col items-center gap-1 rounded-[14px] px-3 py-3 text-center sm:px-4"
             style="background:var(--color-surface); border:1px solid var(--color-border-subtle); box-shadow:var(--shadow-soft)">
            <p class="text-[11px] font-semibold uppercase tracking-wider sm:text-xs" style="color:var(--color-text-muted)">Total Requests</p>
            <p class="text-2xl font-bold sm:text-3xl" style="color:var(--color-text-main)">{{ fmt(kpi.total_requests) }}</p>
        </div>

        <!-- Input Tokens -->
        <div class="animate-fade-up animate-fade-up-2 flex flex-col items-center gap-1 rounded-[14px] px-3 py-3 text-center sm:px-4"
             style="background:var(--color-surface); border:1px solid var(--color-border-subtle); box-shadow:var(--shadow-soft)">
            <p class="text-[11px] font-semibold uppercase tracking-wider sm:text-xs" style="color:var(--color-text-muted)">Total Input Tokens</p>
            <p class="text-2xl font-bold sm:text-3xl" style="color:#f4661f">{{ fmt(kpi.total_input_tokens) }}</p>
        </div>

        <!-- Output Tokens -->
        <div class="animate-fade-up animate-fade-up-3 flex flex-col items-center gap-1 rounded-[14px] px-3 py-3 text-center sm:px-4"
             style="background:var(--color-surface); border:1px solid var(--color-border-subtle); box-shadow:var(--shadow-soft)">
            <p class="text-[11px] font-semibold uppercase tracking-wider sm:text-xs" style="color:var(--color-text-muted)">Output Tokens</p>
            <p class="text-2xl font-bold sm:text-3xl" style="color:#16a34a">{{ fmt(kpi.total_output_tokens) }}</p>
        </div>

        <!-- Total Tokens -->
        <div class="animate-fade-up animate-fade-up-4 flex flex-col items-center gap-1 rounded-[14px] px-3 py-3 text-center sm:px-4"
             style="background:var(--color-surface); border:1px solid var(--color-border-subtle); box-shadow:var(--shadow-soft)">
            <p class="text-[11px] font-semibold uppercase tracking-wider sm:text-xs" style="color:var(--color-text-muted)">Total Tokens</p>
            <p class="text-2xl font-bold sm:text-3xl" style="color:#2563eb">{{ fmt(kpi.total_tokens) }}</p>
        </div>

        <!-- Est. Cost -->
        <div class="animate-fade-up animate-fade-up-5 flex flex-col items-center gap-1 rounded-[14px] px-3 py-3 text-center sm:px-4"
             style="background:var(--color-surface); border:1px solid var(--color-border-subtle); box-shadow:var(--shadow-soft)">
            <p class="text-[11px] font-semibold uppercase tracking-wider sm:text-xs" style="color:var(--color-text-muted)">Est. Cost</p>
            <p class="text-2xl font-bold sm:text-3xl" style="color:#f59e0b">{{ fmtCost(kpi.est_cost) }}</p>
            <p class="text-[10px]" style="color:var(--color-text-subtle)">Estimated, not actual billing.</p>
        </div>

    </div>
</template>
