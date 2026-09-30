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
            label:                metrics.find(m => m.value === metric.value)?.label,
            data:                 data ?? [],
            borderColor:          '#f97316',
            backgroundColor:      'rgba(249,115,22,0.07)',
            tension:              0.3,
            fill:                 true,
            pointRadius:          (props.chart.labels?.length ?? 0) > 60 ? 0 : 3,
            pointHoverRadius:     5,
            pointBackgroundColor: '#f97316',
            borderWidth:          2,
        }],
    };
});

const options = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: 'white',
            borderColor:     '#e5e7eb',
            borderWidth:     1,
            titleColor:      '#6b7280',
            bodyColor:       '#111827',
            padding:         10,
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
            ticks:  { color: '#9ca3af', font: { size: 11 }, maxTicksLimit: 10 },
            grid:   { color: 'rgba(0,0,0,0.04)' },
            border: { color: '#f3f4f6' },
        },
        y: {
            beginAtZero: true,
            ticks: {
                color: '#9ca3af',
                font:  { size: 11 },
                callback: (v) => v >= 1000 ? (v/1000).toFixed(0)+'K' : v,
            },
            grid:   { color: 'rgba(0,0,0,0.04)' },
            border: { color: '#f3f4f6' },
        },
    },
}));
</script>

<template>
    <div class="rounded-[12px] border border-gray-100 bg-white p-5 shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
        <div class="mb-4 flex items-center justify-between">
            <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-gray-400">Usage Trend</p>

            <!-- Metric toggle -->
            <div class="flex rounded-full p-[3px]" style="background: rgba(0,0,0,0.05)">
                <button
                    v-for="m in metrics"
                    :key="m.value"
                    type="button"
                    :class="[
                        'rounded-full px-[14px] py-[5px] text-[13px] font-medium transition-all focus:outline-none',
                        metric === m.value
                            ? 'bg-orange-500 text-white shadow-sm'
                            : 'text-gray-500 hover:text-gray-800',
                    ]"
                    @click="metric = m.value"
                >
                    {{ m.label }}
                </button>
            </div>
        </div>

        <div v-if="!chart.labels || chart.labels.length === 0"
             class="flex h-44 items-center justify-center text-[13px] text-gray-400">
            No data for this period
        </div>
        <div v-else class="h-52">
            <Line :data="chartData" :options="options" />
        </div>
    </div>
</template>
