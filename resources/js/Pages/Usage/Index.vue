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
    return '~$' + (n < 1 ? n.toFixed(6).replace(/\.?0+$/, '') : n.toFixed(2));
}
</script>

<template>
    <AppLayout title="Usage & Analytics">
        <!-- Landing grid background -->
        <div class="relative min-h-full">
            <div class="landing-grid pointer-events-none absolute inset-0 -z-10" aria-hidden="true"></div>

            <div class="mx-auto max-w-7xl px-4 py-6 lg:px-8 lg:py-8">

                <!-- Page header -->
                <header class="mb-2 flex items-start justify-between gap-3 border-b pb-4" style="border-color:var(--color-border-subtle)">
                    <div>
                        <div class="flex items-center gap-2">
                            <svg class="h-5 w-5 lg:h-6 lg:w-6" style="color:var(--color-brand-500)" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <h1 class="text-xl font-semibold tracking-tight lg:text-2xl" style="color:var(--color-text-main)">Usage &amp; Analytics</h1>
                        </div>
                        <p class="mt-0.5 hidden text-[13px] lg:block" style="color:var(--color-text-muted)">
                            Monitor your API usage, token consumption, and request logs
                        </p>
                    </div>
                </header>

                <!-- Controls row: tabs + time range -->
                <div class="mb-5 mt-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <!-- Tab switcher — bg-surface-2 container, bg-surface active -->
                    <div class="inline-flex w-full items-center overflow-x-auto rounded-[10px] p-1 sm:w-auto"
                         style="background:var(--color-surface-2)">
                        <button
                            v-for="t in ['overview', 'details']"
                            :key="t"
                            type="button"
                            :class="['shrink-0 rounded-[8px] px-4 py-[6px] text-[13px] font-medium capitalize transition-all focus:outline-none', tab === t ? 'shadow-sm' : '']"
                            :style="tab === t
                                ? 'background:var(--color-surface); color:var(--color-text-main)'
                                : 'color:var(--color-text-muted)'"
                            @click="switchTab(t)"
                        >
                            {{ t }}
                        </button>
                    </div>

                    <!-- Time range — same container style -->
                    <div class="inline-flex w-full items-center overflow-x-auto rounded-[10px] p-1 sm:w-auto"
                         style="background:var(--color-surface-2)">
                        <button
                            v-for="r in RANGES"
                            :key="r.value"
                            type="button"
                            :class="['shrink-0 rounded-[8px] px-4 py-[5px] text-xs font-medium transition-all focus:outline-none', range === r.value ? 'shadow-sm' : '']"
                            :style="range === r.value
                                ? 'background:var(--color-surface); color:var(--color-text-main)'
                                : 'color:var(--color-text-muted)'"
                            @click="router.get(route('usage.index'), { range: r.value, tab: tab }, { preserveState: false })"
                        >
                            {{ r.label }}
                        </button>
                    </div>
                </div>

                <!-- KPI cards -->
                <div class="mb-5">
                    <KpiCards :kpi="kpi" />
                </div>

                <!-- ── Overview ── -->
                <template v-if="tab === 'overview'">
                    <div class="mb-4 grid grid-cols-1 gap-4 lg:grid-cols-12">
                        <!-- Topology 8/12 -->
                        <div class="lg:col-span-8">
                            <AgentTopologyGraph :nodes="nodes" @select-agent="onSelectAgent" />

                            <!-- Agent info panel — slide-fade -->
                            <transition
                                enter-active-class="transition duration-200 ease-out"
                                enter-from-class="opacity-0 translate-y-1 scale-[0.98]"
                                enter-to-class="opacity-100 translate-y-0 scale-100"
                                leave-active-class="transition duration-150 ease-in"
                                leave-from-class="opacity-100 scale-100"
                                leave-to-class="opacity-0 scale-[0.98]"
                            >
                                <div
                                    v-if="selectedAgent"
                                    class="mt-3 rounded-[14px] p-5"
                                    style="background:var(--color-surface); border:1px solid var(--color-border-subtle); box-shadow:var(--shadow-soft)"
                                >
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <p class="text-[16px] font-semibold" style="color:var(--color-text-main)">{{ selectedAgent.name }}</p>
                                            <p class="mt-0.5 text-[13px]" style="color:var(--color-text-muted)">
                                                {{ selectedAgent.provider }} · {{ selectedAgent.model_default }}
                                            </p>
                                        </div>
                                        <button
                                            type="button"
                                            class="rounded-full p-1 text-[11px] transition-colors"
                                            style="color:var(--color-text-subtle)"
                                            onmouseenter="this.style.color='var(--color-text-main)'"
                                            onmouseleave="this.style.color='var(--color-text-subtle)'"
                                            @click="selectedAgent = null"
                                        >✕</button>
                                    </div>
                                    <div class="mt-4 grid grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-[11px] font-semibold uppercase tracking-[0.08em]" style="color:var(--color-text-subtle)">Tokens (range)</p>
                                            <p class="mt-1 text-[24px] font-bold" style="color:#3b82f6">{{ selectedAgent.total_tokens.toLocaleString() }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[11px] font-semibold uppercase tracking-[0.08em]" style="color:var(--color-text-subtle)">Cost (range)</p>
                                            <p class="mt-1 text-[24px] font-bold" style="color:#f59e0b">{{ fmtCost(selectedAgent.total_cost) }}</p>
                                        </div>
                                    </div>
                                </div>
                            </transition>
                        </div>

                        <!-- Recent log 4/12 -->
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
    </AppLayout>
</template>
