<script setup>
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale, LinearScale, BarElement,
    Title, Tooltip, Legend,
} from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

const PALETTE = [
    '#6366f1','#8b5cf6','#ec4899','#f59e0b',
    '#10b981','#3b82f6','#ef4444','#14b8a6',
    '#f97316','#84cc16',
];

const props = defineProps({
    tokenPerAgent: { type: Array, default: () => [] },
});

const chartData = computed(() => ({
    labels: props.tokenPerAgent.map(a => a.is_deleted ? a.agent_name + ' (deleted)' : a.agent_name),
    datasets: [{
        label: 'Total Tokens',
        data:  props.tokenPerAgent.map(a => a.total_tokens),
        backgroundColor: props.tokenPerAgent.map((_, i) => PALETTE[i % PALETTE.length]),
        borderRadius: 4,
    }],
}));

const options = {
    indexAxis: 'y',
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            callbacks: {
                label: (ctx) => ` ${ctx.parsed.x.toLocaleString()} tokens`,
            },
        },
    },
    scales: {
        x: {
            beginAtZero: true,
            ticks: { callback: (v) => v >= 1000 ? (v/1000).toFixed(0)+'K' : v },
        },
    },
};
</script>

<template>
    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-6 py-4">
            <h2 class="text-sm font-semibold text-gray-900">Tokens per Agent</h2>
        </div>
        <div class="p-4">
            <div v-if="tokenPerAgent.length === 0" class="flex h-48 items-center justify-center text-sm text-gray-400">
                No data for this period
            </div>
            <div v-else :style="{ height: Math.max(160, tokenPerAgent.length * 36) + 'px' }">
                <Bar :data="chartData" :options="options" />
            </div>
        </div>
    </div>
</template>
