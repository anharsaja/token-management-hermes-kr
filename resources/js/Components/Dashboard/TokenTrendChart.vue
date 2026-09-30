<script setup>
import { computed } from 'vue';
import { Line } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale, LinearScale, PointElement,
    LineElement, Title, Tooltip, Legend, Filler,
} from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler);

const props = defineProps({
    tokenTrend:   { type: Array,  default: () => [] },
    period:       { type: String, default: 'month' },
    groupByMonth: { type: Boolean, default: false },
});

// Fill gaps: build a complete date/month series with 0 for missing days
const chartData = computed(() => {
    if (props.tokenTrend.length === 0) {
        return { labels: [], datasets: [] };
    }

    const map = Object.fromEntries(props.tokenTrend.map(r => [r.date, r.total_tokens]));

    let labels = [];

    if (props.groupByMonth) {
        // Already grouped by month server-side — use as-is
        labels = props.tokenTrend.map(r => r.date);
    } else {
        // Fill every day in range
        const dates = props.tokenTrend.map(r => r.date).sort();
        const start = new Date(dates[0]);
        const end   = new Date(dates[dates.length - 1]);
        const cur   = new Date(start);
        while (cur <= end) {
            labels.push(cur.toISOString().slice(0, 10));
            cur.setDate(cur.getDate() + 1);
        }
    }

    const data = labels.map(d => map[d] ?? 0);

    const fmt = (d) => {
        const dt = new Date(d + 'T00:00:00');
        if (props.groupByMonth) {
            return dt.toLocaleDateString('en-GB', { month: 'short', year: 'numeric' });
        }
        return props.period === 'year'
            ? dt.toLocaleDateString('en-GB', { day: '2-digit', month: 'short' })
            : dt.toLocaleDateString('en-GB', { day: '2-digit', month: 'short' });
    };

    return {
        labels: labels.map(fmt),
        datasets: [{
            label: 'Total Tokens',
            data,
            borderColor:     '#6366f1',
            backgroundColor: 'rgba(99,102,241,0.1)',
            tension:         0.3,
            fill:            true,
            pointRadius:     labels.length > 60 ? 0 : 3,
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
                label: (ctx) => ` ${ctx.parsed.y.toLocaleString()} tokens`,
            },
        },
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: { callback: (v) => v >= 1000 ? (v/1000).toFixed(0)+'K' : v },
        },
    },
};
</script>

<template>
    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-6 py-4">
            <h2 class="text-sm font-semibold text-gray-900">Token Usage Trend</h2>
        </div>
        <div class="p-4">
            <div v-if="tokenTrend.length === 0" class="flex h-48 items-center justify-center text-sm text-gray-400">
                No data for this period
            </div>
            <div v-else class="h-48">
                <Line :data="chartData" :options="options" />
            </div>
        </div>
    </div>
</template>
