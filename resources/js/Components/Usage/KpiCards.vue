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
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">

        <!-- Total Requests -->
        <div class="rounded-[14px] border border-white/[0.07] p-5 backdrop-blur-[12px]"
             style="background: rgba(17,24,39,0.60)">
            <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-[#94A3B8]">Requests</p>
            <p class="mt-2 text-[30px] font-bold leading-none text-[#F1F5F9]">{{ fmt(kpi.total_requests) }}</p>
        </div>

        <!-- Input Tokens -->
        <div class="rounded-[14px] border border-white/[0.07] p-5 backdrop-blur-[12px]"
             style="background: rgba(17,24,39,0.60)">
            <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-[#94A3B8]">Input Tokens</p>
            <p class="mt-2 text-[30px] font-bold leading-none text-[#F97316]">{{ fmt(kpi.total_input_tokens) }}</p>
        </div>

        <!-- Output Tokens -->
        <div class="rounded-[14px] border border-white/[0.07] p-5 backdrop-blur-[12px]"
             style="background: rgba(17,24,39,0.60)">
            <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-[#94A3B8]">Output Tokens</p>
            <p class="mt-2 text-[30px] font-bold leading-none text-[#2DD4BF]">{{ fmt(kpi.total_output_tokens) }}</p>
        </div>

        <!-- Total Tokens -->
        <div class="rounded-[14px] border border-white/[0.07] p-5 backdrop-blur-[12px]"
             style="background: rgba(17,24,39,0.60)">
            <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-[#94A3B8]">Total Tokens</p>
            <p class="mt-2 text-[30px] font-bold leading-none text-[#818CF8]">{{ fmt(kpi.total_tokens) }}</p>
        </div>

        <!-- Est. Cost -->
        <div class="rounded-[14px] border border-white/[0.07] p-5 backdrop-blur-[12px]"
             style="background: rgba(17,24,39,0.60)">
            <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-[#94A3B8]">Est. Cost</p>
            <p class="mt-2 text-[30px] font-bold leading-none text-[#FBBF24]">{{ fmtCost(kpi.est_cost) }}</p>
            <p class="mt-1.5 text-[11px] text-[#475569]">Manually recorded</p>
        </div>

    </div>
</template>
