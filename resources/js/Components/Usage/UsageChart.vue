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
            borderColor:          '#e56a4a',
            backgroundColor:      'rgba(229,106,74,0.06)',
            tension:              0.4,
            fill:                 true,
            pointRadius:          (props.chart.labels?.length ?? 0) > 60 ? 0 : 3,
            pointHoverRadius:     5,
            pointBackgroundColor: '#e56a4a',
            pointBorderColor:     '#fff',
            pointBorderWidth:     1.5,
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
            backgroundColor: '#ffffff',
            borderColor:     '#e5e7eb',
            borderWidth:     1,
            titleColor:      '#6b7280',
            bodyColor:       '#0a0a0a',
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
            ticks:  { color: '#9ca3af', font: { size: 11, family: 'Inter, system-ui' }, maxTicksLimit: 10 },
            grid:   { color: 'rgba(229,106,74,0.05)' },
            border: { color: '#f1f1f3' },
        },
        y: {
            beginAtZero: true,
            ticks: {
                color: '#9ca3af',
                font:  { size: 11, family: 'Inter, system-ui' },
                callback: (v) => v >= 1000 ? (v/1000).toFixed(0)+'K' : v,
            },
            grid:   { color: 'rgba(229,106,74,0.05)' },
            border: { color: '#f1f1f3' },
        },
    },
}));
</script>

<template>
    <div class="rounded-[14px] p-5"
         style="background:var(--color-surface); border:1px solid var(--color-border-subtle); box-shadow:var(--shadow-soft)">

        <div class="mb-4 flex items-center justify-between">
            <p class="text-[11px] font-semibold uppercase tracking-wider" style="color:var(--color-text-muted)">Usage Trend</p>

            <!-- Metric toggle — surface-2 container, surface active (exact 9Router style) -->
            <div class="inline-flex items-center rounded-[8px] p-1" style="background:var(--color-surface-2)">
                <button
                    v-for="m in metrics"
                    :key="m.value"
                    type="button"
                    :class="['rounded-[6px] px-3 py-1 text-[12px] font-medium transition-all focus:outline-none', metric === m.value ? 'shadow-sm' : '']"
                    :style="metric === m.value
                        ? 'background:var(--color-surface); color:var(--color-text-main)'
                        : 'color:var(--color-text-muted)'"
                    @click="metric = m.value"
                >
                    {{ m.label }}
                </button>
            </div>
        </div>

        <div v-if="!chart.labels || chart.labels.length === 0"
             class="flex h-44 items-center justify-center text-[13px]"
             style="color:var(--color-text-subtle)">
            No data for this period
        </div>
        <div v-else class="h-52">
            <Line :data="chartData" :options="options" />
        </div>
    </div>
</template>
