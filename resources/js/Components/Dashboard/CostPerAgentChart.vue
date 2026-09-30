<script setup>
import { computed } from 'vue';
import { Doughnut } from 'vue-chartjs';
import {
    Chart as ChartJS,
    ArcElement, Tooltip, Legend,
} from 'chart.js';

ChartJS.register(ArcElement, Tooltip, Legend);

const PALETTE = [
    '#6366f1','#8b5cf6','#ec4899','#f59e0b',
    '#10b981','#3b82f6','#ef4444','#14b8a6',
    '#f97316','#84cc16',
];

const props = defineProps({
    costPerAgent: { type: Array, default: () => [] },
});

const hasCost = computed(() => props.costPerAgent.length > 0);

const chartData = computed(() => ({
    labels: props.costPerAgent.map(a => a.agent_name),
    datasets: [{
        data:            props.costPerAgent.map(a => a.total_cost),
        backgroundColor: props.costPerAgent.map((_, i) => PALETTE[i % PALETTE.length]),
        borderWidth:     2,
        borderColor:     '#fff',
    }],
}));

function formatCost(c) {
    const n = Number(c);
    return n < 1 ? '$' + n.toFixed(6).replace(/\.?0+$/, '') : '$' + n.toFixed(2);
}

const options = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'bottom', labels: { boxWidth: 12, padding: 12 } },
        tooltip: {
            callbacks: {
                label: (ctx) => ` ${ctx.label}: ${formatCost(ctx.parsed)}`,
            },
        },
    },
};
</script>

<template>
    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-6 py-4">
            <h2 class="text-sm font-semibold text-gray-900">Cost Distribution per Agent</h2>
        </div>
        <div class="p-4">
            <div v-if="!hasCost" class="flex h-48 items-center justify-center text-sm text-gray-400">
                No cost data for this period
            </div>
            <div v-else class="h-48">
                <Doughnut :data="chartData" :options="options" />
            </div>
        </div>
    </div>
</template>
