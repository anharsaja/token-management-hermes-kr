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

// Selected agent info panel from topology graph
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
    <!-- Full dark page — no AppLayout, own shell -->
    <div class="min-h-screen bg-gray-950 text-gray-100">

        <!-- Top nav bar -->
        <header class="flex h-14 items-center justify-between border-b border-gray-800 bg-gray-900/80 px-6 backdrop-blur">
            <div class="flex items-center gap-3">
                <a href="/dashboard" class="text-xs text-gray-500 hover:text-gray-300">← Dashboard</a>
                <span class="text-gray-700">/</span>
                <span class="text-sm font-semibold text-white">Usage &amp; Analytics</span>
            </div>
            <TimeRangeSelector :ranges="RANGES" :active-range="range" :active-tab="tab" />
        </header>

        <div class="mx-auto max-w-screen-xl px-4 py-6 sm:px-6 lg:px-8">

            <!-- Page title + tab switcher -->
            <div class="mb-5 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-white">Usage &amp; Analytics</h1>
                    <p class="mt-0.5 text-xs text-gray-500">Token consumption across all agents</p>
                </div>
                <!-- Tab switcher -->
                <div class="flex gap-1 rounded-lg bg-gray-800 p-1">
                    <button
                        v-for="t in ['overview', 'details']"
                        :key="t"
                        type="button"
                        :class="[
                            'rounded-md px-4 py-1.5 text-xs font-semibold capitalize transition-colors focus:outline-none',
                            tab === t ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:text-white',
                        ]"
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

            <!-- ── Overview tab ── -->
            <template v-if="tab === 'overview'">
                <!-- Top row: topology (left) + recent log (right) -->
                <div class="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-3">
                    <div class="lg:col-span-2">
                        <AgentTopologyGraph :nodes="nodes" @select-agent="onSelectAgent" />

                        <!-- Agent info panel (shown after clicking a node) -->
                        <transition
                            enter-active-class="transition duration-150 ease-out"
                            enter-from-class="opacity-0 -translate-y-1"
                            enter-to-class="opacity-100 translate-y-0"
                            leave-active-class="transition duration-100 ease-in"
                            leave-from-class="opacity-100"
                            leave-to-class="opacity-0"
                        >
                            <div
                                v-if="selectedAgent"
                                class="mt-3 rounded-xl border border-indigo-600/40 bg-gray-800/80 px-5 py-4 backdrop-blur"
                            >
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <p class="font-semibold text-white">{{ selectedAgent.name }}</p>
                                        <p class="text-xs text-gray-400">{{ selectedAgent.provider }} · {{ selectedAgent.model_default }}</p>
                                    </div>
                                    <button type="button" class="text-gray-500 hover:text-white text-xs" @click="selectedAgent = null">✕</button>
                                </div>
                                <div class="mt-3 grid grid-cols-2 gap-3 text-xs">
                                    <div>
                                        <p class="text-gray-500">Tokens (range)</p>
                                        <p class="text-lg font-bold text-indigo-300">{{ selectedAgent.total_tokens.toLocaleString() }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500">Cost (range)</p>
                                        <p class="text-lg font-bold text-yellow-300">{{ fmtCost(selectedAgent.total_cost) }}</p>
                                    </div>
                                </div>
                            </div>
                        </transition>
                    </div>
                    <div class="lg:col-span-1">
                        <RecentLogTable :recent-log="recent_log" />
                    </div>
                </div>

                <!-- Bottom: usage trend chart full width -->
                <UsageChart :chart="chart" />
            </template>

            <!-- ── Details tab ── -->
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
