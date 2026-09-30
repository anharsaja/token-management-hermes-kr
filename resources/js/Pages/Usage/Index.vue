<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import KpiCards from '@/Components/Usage/KpiCards.vue';
import TimeRangeSelector from '@/Components/Usage/TimeRangeSelector.vue';
import AgentTopologyGraph from '@/Components/Usage/AgentTopologyGraph.vue';
import RecentLogTable from '@/Components/Usage/RecentLogTable.vue';
import UsageChart from '@/Components/Usage/UsageChart.vue';
import DetailsTable from '@/Components/Usage/DetailsTable.vue';

const props = defineProps({
    range:      { type: String,  default: '7d' },
    tab:        { type: String,  default: 'overview' },
    kpi:        { type: Object,  default: () => ({}) },
    nodes:      { type: Array,   default: () => [] },
    recent_log: { type: Array,   default: () => [] },
    chart:      { type: Object,  default: () => ({}) },
    usages:     { type: Object,  default: null },
    agents:     { type: Array,   default: null },
    filters:    { type: Object,  default: null },
});

const RANGES = [
    { value: 'today', label: 'Today' },
    { value: '7d',    label: '7D' },
    { value: '30d',   label: '30D' },
    { value: '60d',   label: '60D' },
    { value: 'all',   label: 'All' },
];

function switchTab(t) {
    router.get(route('usage.index'), { range: props.range, tab: t }, { preserveState: false });
}

const selectedAgent = ref(null);

function onSelectAgent(node) {
    selectedAgent.value = node;
}

function fmtCost(c) {
    if (c === null || c === undefined) return '—';
    const n = Number(c);
    return n < 1 ? '$' + n.toFixed(6).replace(/\.?0+$/, '') : '$' + n.toFixed(2);
}
</script>

<template>
    <!-- bg-base: #0D1520 -->
    <div class="min-h-screen" style="background:#0D1520; color:#F1F5F9; font-family:'Inter','DM Sans',system-ui,sans-serif">

        <!-- Top bar: bg-surface with glass -->
        <header
            class="flex h-[60px] items-center justify-between px-6 backdrop-blur-[12px]"
            style="background:rgba(17,24,39,0.80); border-bottom:1px solid rgba(255,255,255,0.07)"
        >
            <div class="flex items-center gap-3">
                <a
                    href="/dashboard"
                    class="text-[13px] transition-colors"
                    style="color:#475569"
                    onmouseenter="this.style.color='#94A3B8'"
                    onmouseleave="this.style.color='#475569'"
                >← Dashboard</a>
                <span style="color:#1E293B">/</span>
                <span class="text-[14px] font-semibold" style="color:#F1F5F9">Usage &amp; Analytics</span>
            </div>
            <TimeRangeSelector :ranges="RANGES" :active-range="range" :active-tab="tab" />
        </header>

        <!-- Page content -->
        <div class="px-6 py-6" style="max-width:1400px; margin:0 auto">

            <!-- Page title + tab switcher -->
            <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h1 class="text-[24px] font-semibold leading-[32px]" style="color:#F1F5F9">Usage &amp; Analytics</h1>
                    <p class="mt-1 text-[14px]" style="color:#94A3B8">Token consumption across all agents</p>
                </div>

                <!-- Tab switcher — pill style -->
                <div
                    class="flex rounded-full p-[3px]"
                    style="background:rgba(255,255,255,0.05)"
                >
                    <button
                        v-for="t in ['overview', 'details']"
                        :key="t"
                        type="button"
                        :class="[
                            'rounded-full px-5 py-[6px] text-[13px] font-medium capitalize transition-colors focus:outline-none',
                            tab === t
                                ? 'bg-[#818CF8] text-white font-semibold'
                                : 'hover:text-[#F1F5F9]',
                        ]"
                        :style="tab !== t ? 'color:#94A3B8' : ''"
                        @click="switchTab(t)"
                    >
                        {{ t }}
                    </button>
                </div>
            </div>

            <!-- KPI cards -->
            <div class="mb-6">
                <KpiCards :kpi="kpi" />
            </div>

            <!-- ── Overview ── -->
            <template v-if="tab === 'overview'">

                <!-- Topology (8/12) + Recent log (4/12) -->
                <div class="mb-4 grid grid-cols-1 gap-4 lg:grid-cols-12">
                    <div class="lg:col-span-8">
                        <AgentTopologyGraph :nodes="nodes" @select-agent="onSelectAgent" />

                        <!-- Agent info panel -->
                        <transition
                            enter-active-class="transition duration-150 ease-out"
                            enter-from-class="opacity-0 scale-[0.96]"
                            enter-to-class="opacity-100 scale-100"
                            leave-active-class="transition duration-100 ease-in"
                            leave-from-class="opacity-100 scale-100"
                            leave-to-class="opacity-0 scale-[0.96]"
                        >
                            <div
                                v-if="selectedAgent"
                                class="mt-3 rounded-[14px] border p-5 backdrop-blur-[12px]"
                                style="background:rgba(17,24,39,0.80); border-color:rgba(129,140,248,0.30)"
                            >
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <p class="text-[16px] font-semibold" style="color:#F1F5F9">{{ selectedAgent.name }}</p>
                                        <p class="mt-0.5 text-[13px]" style="color:#94A3B8">
                                            {{ selectedAgent.provider }} · {{ selectedAgent.model_default }}
                                        </p>
                                    </div>
                                    <button
                                        type="button"
                                        class="rounded-full p-1 text-[11px] transition-colors"
                                        style="color:#475569"
                                        onmouseenter="this.style.color='#F1F5F9'"
                                        onmouseleave="this.style.color='#475569'"
                                        @click="selectedAgent = null"
                                    >✕</button>
                                </div>
                                <div class="mt-4 grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-[11px] font-medium uppercase tracking-[0.08em]" style="color:#94A3B8">Tokens (range)</p>
                                        <p class="mt-1 text-[24px] font-bold" style="color:#818CF8">
                                            {{ selectedAgent.total_tokens.toLocaleString() }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-[11px] font-medium uppercase tracking-[0.08em]" style="color:#94A3B8">Cost (range)</p>
                                        <p class="mt-1 text-[24px] font-bold" style="color:#FBBF24">{{ fmtCost(selectedAgent.total_cost) }}</p>
                                    </div>
                                </div>
                            </div>
                        </transition>
                    </div>

                    <div class="lg:col-span-4">
                        <RecentLogTable :recent-log="recent_log" />
                    </div>
                </div>

                <!-- Chart full width -->
                <UsageChart :chart="chart" />
            </template>

            <!-- ── Details ── -->
            <template v-if="tab === 'details'">
                <DetailsTable
                    :usages="usages"
                    :agents="agents ?? []"
                    :filters="filters ?? {}"
                    :range="range"
                />
            </template>
        </div>
    </div>
</template>
