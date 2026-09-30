<script setup>
import { ref, computed } from 'vue';
import { Line } from 'vue-chartjs';
import {
    Chart as ChartJS, CategoryScale, LinearScale, PointElement,
    LineElement, Tooltip, Legend, Filler,
} from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Tooltip, Legend, Filler);

const props = defineProps({
    chart: { type: Object, default: () => ({ labels: [], tokens: [], requests: [], costs: [] }) },
});

const metric = ref('tokens');
const metrics = [
    { value: 'tokens',   label: 'Tokens' },
    { value: 'requests', label: 'Requests' },
    { value: 'cost',     label: 'Cost' },
];

const chartData = computed(() => {
    const data = metric.value === 'tokens'   ? props.chart.tokens
               : metric.value === 'requests' ? props.chart.requests
               : props.chart.costs;
    return {
        labels: props.chart.labels ?? [],
        datasets: [{
            label: metrics.find(m => m.value === metric.value)?.label,
            data:  data ?? [],
            borderColor:     '#6366f1',
            backgroundColor: 'rgba(99,102,241,0.15)',
            tension:         0.3,
            fill:            true,
            pointRadius:     (props.chart.labels?.length ?? 0) > 60 ? 0 : 3,
            pointBackgroundColor: '#6366f1',
        }],
    };
});

const options = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            callbacks: {
                label: (ctx) => {
                    const v = ctx.parsed.y;
                    if (metric.value === 'cost') return ` $${v.toFixed(6).replace(/\.?0+$/, '')}`;
                    return ` ${v.toLocaleString()}`;
                },
            },
        },
    },
    scales: {
        x: {
            ticks: { color: '#6b7280', maxTicksLimit: 10 },
            grid:  { color: 'rgba(107,114,128,0.15)' },
        },
        y: {
            beginAtZero: true,
            ticks: {
                color: '#6b7280',
                callback: (v) => v >= 1000 ? (v/1000).toFixed(0)+'K' : v,
            },
            grid: { color: 'rgba(107,114,128,0.15)' },
        },
    },
};
</script>

<template>
    <div class="rounded-xl border border-gray-700 bg-gray-800/60 p-4 backdrop-blur">
        <div class="mb-3 flex items-center justify-between">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Usage Trend</p>
            <!-- Metric toggle -->
            <div class="flex gap-1 rounded-lg bg-gray-900 p-1">
                <button
                    v-for="m in metrics"
                    :key="m.value"
                    type="button"
                    :class="[
                        'rounded px-3 py-1 text-xs font-semibold transition-colors focus:outline-none',
                        metric === m.value ? 'bg-orange-500 text-white' : 'text-gray-400 hover:text-white',
                    ]"
                    @click="metric = m.value"
                >
                    {{ m.label }}
                </button>
            </div>
        </div>
        <div v-if="!chart.labels || chart.labels.length === 0" class="flex h-40 items-center justify-center text-sm text-gray-500">
            No data for this period
        </div>
        <div v-else class="h-48">
            <Line :data="chartData" :options="options" />
        </div>
    </div>
</template>
