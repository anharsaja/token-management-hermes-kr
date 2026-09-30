<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
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
    <AppLayout title="Usage & Analytics">
        <div class="p-6">

            <!-- Page header -->
            <div class="mb-5">
                <div class="flex items-start gap-2">
                    <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <div>
                        <h1 class="text-[24px] font-semibold text-gray-900">Usage &amp; Analytics</h1>
                        <p class="mt-0.5 text-[14px] text-gray-500">Monitor your API usage, token consumption, and request logs</p>
                    </div>
                </div>

                <!-- Tabs + Time Range row -->
                <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                    <!-- Tab switcher — segment style matching reference -->
                    <div class="flex rounded-[10px] border border-gray-200 bg-white p-1 shadow-sm">
                        <button
                            v-for="t in ['overview', 'details']"
                            :key="t"
                            type="button"
                            :class="[
                                'rounded-[8px] px-5 py-1.5 text-[13px] font-medium capitalize transition-all focus:outline-none',
                                tab === t
                                    ? 'bg-white text-gray-900 shadow-sm ring-1 ring-gray-200'
                                    : 'text-gray-500 hover:text-gray-700',
                            ]"
                            @click="switchTab(t)"
                        >
                            {{ t }}
                        </button>
                    </div>

                    <TimeRangeSelector :ranges="RANGES" :active-range="range" :active-tab="tab" />
                </div>
            </div>

            <!-- KPI cards -->
            <div class="mb-5">
                <KpiCards :kpi="kpi" />
            </div>

            <!-- ── Overview ── -->
            <template v-if="tab === 'overview'">

                <!-- Topology (8/12) + Recent requests (4/12) -->
                <div class="mb-5 grid grid-cols-1 gap-4 lg:grid-cols-12">
                    <div class="lg:col-span-8">
                        <AgentTopologyGraph :nodes="nodes" @select-agent="onSelectAgent" />

                        <!-- Agent info panel -->
                        <transition
                            enter-active-class="transition duration-150 ease-out"
                            enter-from-class="opacity-0 scale-[0.97]"
                            enter-to-class="opacity-100 scale-100"
                            leave-active-class="transition duration-100 ease-in"
                            leave-from-class="opacity-100"
                            leave-to-class="opacity-0"
                        >
                            <div
                                v-if="selectedAgent"
                                class="mt-3 rounded-[12px] border border-orange-100 bg-orange-50/50 p-5"
                            >
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <p class="text-[16px] font-semibold text-gray-900">{{ selectedAgent.name }}</p>
                                        <p class="mt-0.5 text-[13px] text-gray-500">
                                            {{ selectedAgent.provider }} · {{ selectedAgent.model_default }}
                                        </p>
                                    </div>
                                    <button
                                        type="button"
                                        class="rounded-full p-1 text-[11px] text-gray-400 transition-colors hover:text-gray-700"
                                        @click="selectedAgent = null"
                                    >✕</button>
                                </div>
                                <div class="mt-4 grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-gray-400">Tokens (range)</p>
                                        <p class="mt-1 text-[24px] font-bold text-blue-500">{{ selectedAgent.total_tokens.toLocaleString() }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-gray-400">Cost (range)</p>
                                        <p class="mt-1 text-[24px] font-bold text-orange-500">{{ fmtCost(selectedAgent.total_cost) }}</p>
                                    </div>
                                </div>
                            </div>
                        </transition>
                    </div>

                    <div class="lg:col-span-4">
                        <RecentLogTable :recent-log="recent_log" />
                    </div>
                </div>

                <!-- Chart -->
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
    </AppLayout>
</template>
