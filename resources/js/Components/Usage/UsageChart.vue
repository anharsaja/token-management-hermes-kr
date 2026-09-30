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
            borderColor:          '#818CF8',
            backgroundColor:      'rgba(129,140,248,0.12)',
            tension:              0.3,
            fill:                 true,
            pointRadius:          (props.chart.labels?.length ?? 0) > 60 ? 0 : 3,
            pointHoverRadius:     5,
            pointBackgroundColor: '#818CF8',
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
            backgroundColor: '#1E293B',
            borderColor:     'rgba(255,255,255,0.07)',
            borderWidth:      1,
            titleColor:       '#94A3B8',
            bodyColor:        '#F1F5F9',
            padding:          10,
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
            ticks: { color: '#475569', font: { size: 11 }, maxTicksLimit: 10 },
            grid:  { color: 'rgba(255,255,255,0.06)' },
            border: { color: 'rgba(255,255,255,0.07)' },
        },
        y: {
            beginAtZero: true,
            ticks: {
                color: '#475569',
                font: { size: 11 },
                callback: (v) => v >= 1000 ? (v/1000).toFixed(0)+'K' : v,
            },
            grid:   { color: 'rgba(255,255,255,0.06)' },
            border: { color: 'rgba(255,255,255,0.07)' },
        },
    },
}));
</script>

<template>
    <div
        class="rounded-[14px] border border-white/[0.07] p-5 backdrop-blur-[12px]"
        style="background: rgba(17,24,39,0.60)"
    >
        <!-- Header row -->
        <div class="mb-4 flex items-center justify-between">
            <p class="text-[11px] font-medium uppercase tracking-[0.08em] text-[#94A3B8]">Usage Trend</p>

            <!-- Metric toggle — pill container -->
            <div
                class="flex rounded-full p-[3px]"
                style="background: rgba(255,255,255,0.05)"
            >
                <button
                    v-for="m in metrics"
                    :key="m.value"
                    type="button"
                    :class="[
                        'rounded-full px-[14px] py-[5px] text-[13px] transition-colors focus:outline-none',
                        metric === m.value ? 'bg-[#F97316] font-semibold text-white' : 'text-[#94A3B8] hover:text-[#F1F5F9]',
                    ]"
                    @click="metric = m.value"
                >
                    {{ m.label }}
                </button>
            </div>
        </div>

        <div
            v-if="!chart.labels || chart.labels.length === 0"
            class="flex h-44 items-center justify-center text-[13px] text-[#475569]"
        >
            No data for this period
        </div>
        <div v-else class="h-52">
            <Line :data="chartData" :options="options" />
        </div>
    </div>
</template>
