<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    usages: { type: Array, default: () => [] },
});

function formatDate(d) {
    return d ? new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '—';
}

function formatCost(c) {
    if (c === null || c === undefined) return '—';
    const n = Number(c);
    return n < 1 ? '$' + n.toFixed(6).replace(/\.?0+$/, '') : '$' + n.toFixed(2);
}
</script>

<template>
    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
            <h2 class="text-sm font-semibold text-gray-900">Recent Token Usage</h2>
            <Link :href="route('token-usages.index')" class="text-xs font-medium text-indigo-600 hover:text-indigo-900">
                View All →
            </Link>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Date</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Agent</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Model</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Input</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Output</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Cost</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                <tr v-if="usages.length === 0">
                    <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">No usage records yet.</td>
                </tr>
                <tr v-for="(u, i) in usages" :key="i" class="hover:bg-gray-50">
                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600">{{ formatDate(u.used_at) }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900">{{ u.agent_name }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ u.model }}</td>
                    <td class="px-4 py-3 text-right text-sm text-gray-600">{{ u.input_tokens.toLocaleString() }}</td>
                    <td class="px-4 py-3 text-right text-sm text-gray-600">{{ u.output_tokens.toLocaleString() }}</td>
                    <td class="px-4 py-3 text-right text-sm text-gray-600">{{ formatCost(u.cost) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
