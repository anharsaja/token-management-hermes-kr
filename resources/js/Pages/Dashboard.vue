<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import SummaryCard from '@/Components/Dashboard/SummaryCard.vue';
import TopAgentsTable from '@/Components/Dashboard/TopAgentsTable.vue';
import RecentUsageTable from '@/Components/Dashboard/RecentUsageTable.vue';
import ProviderBalanceTable from '@/Components/Dashboard/ProviderBalanceTable.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    period:            { type: String, default: 'month' },
    cards:             { type: Object, default: () => ({}) },
    top_agents:        { type: Array,  default: () => [] },
    recent_usages:     { type: Array,  default: () => [] },
    provider_balances: { type: Array,  default: () => [] },
});

const periodOptions = [
    { value: 'week',  label: 'This Week' },
    { value: 'month', label: 'This Month' },
    { value: 'year',  label: 'This Year' },
    { value: 'all',   label: 'All Time' },
];

function setPeriod(p) {
    router.get(route('dashboard'), { period: p }, { preserveState: false });
}

function formatTokens(n) {
    if (n >= 1_000_000) return (n / 1_000_000).toFixed(1) + 'M';
    if (n >= 1_000)     return (n / 1_000).toFixed(1) + 'K';
    return String(n);
}

function formatCost(c) {
    const n = Number(c);
    if (n === 0) return '$0.00';
    return n < 1 ? '$' + n.toFixed(6).replace(/\.?0+$/, '') : '$' + n.toFixed(2);
}

function periodLabel(v) {
    return periodOptions.find(p => p.value === v)?.label?.toLowerCase() ?? '';
}
</script>

<template>
    <AppLayout title="Dashboard">
        <div class="py-8 px-4 sm:px-6 lg:px-8">

            <!-- Header + Period Filter -->
            <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                <h1 class="text-2xl font-semibold text-gray-900">Dashboard</h1>

                <div class="flex rounded-md shadow-sm" role="group" aria-label="Period filter">
                    <button
                        v-for="opt in periodOptions"
                        :key="opt.value"
                        type="button"
                        :class="[
                            'px-4 py-2 text-sm font-medium border border-gray-300 -ml-px first:ml-0 first:rounded-l-md last:rounded-r-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:z-10',
                            period === opt.value
                                ? 'bg-indigo-600 text-white border-indigo-600 z-10'
                                : 'bg-white text-gray-700 hover:bg-gray-50',
                        ]"
                        @click="setPeriod(opt.value)"
                    >
                        {{ opt.label }}
                    </button>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <SummaryCard
                    title="Active Agents"
                    :value="cards.total_agents ?? 0"
                    subtitle="all time"
                />
                <SummaryCard
                    title="Tokens Used"
                    :value="formatTokens(cards.total_tokens ?? 0)"
                    :subtitle="period === 'all' ? 'all time' : periodLabel(period)"
                />
                <SummaryCard
                    title="Total Cost"
                    :value="formatCost(cards.total_cost ?? 0)"
                    :subtitle="period === 'all' ? 'all time' : periodLabel(period)"
                />
                <SummaryCard
                    title="Active Providers"
                    :value="cards.active_providers ?? 0"
                    subtitle="all time"
                />
            </div>

            <!-- Top Agents + Provider Balances -->
            <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
                <TopAgentsTable :agents="top_agents" />
                <ProviderBalanceTable :balances="provider_balances" />
            </div>

            <!-- Recent Usage -->
            <RecentUsageTable :usages="recent_usages" />
        </div>
    </AppLayout>
</template>
