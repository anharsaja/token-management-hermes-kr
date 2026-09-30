<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    agents: { type: Array, default: () => [] },
});

function formatCost(c) {
    if (c === null || c === undefined) return '—';
    const n = Number(c);
    return n < 1 ? '$' + n.toFixed(6).replace(/\.?0+$/, '') : '$' + n.toFixed(2);
}
</script>

<template>
    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-6 py-4">
            <h2 class="text-sm font-semibold text-gray-900">Top Agents by Token Usage</h2>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">#</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Agent</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Model</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Total Tokens</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Total Cost</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Entries</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                <tr v-if="agents.length === 0">
                    <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">No usage data for this period.</td>
                </tr>
                <tr v-for="(a, i) in agents" :key="a.agent_id ?? i" class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm text-gray-500">{{ i + 1 }}</td>
                    <td class="px-4 py-3 text-sm">
                        <Link
                            v-if="a.agent_id"
                            :href="route('token-usages.index', { agent_id: a.agent_id })"
                            class="font-medium text-indigo-600 hover:text-indigo-900"
                        >
                            {{ a.agent_name }}
                        </Link>
                        <span v-else class="text-gray-500">{{ a.agent_name }}</span>
                        <span
                            v-if="a.is_deleted"
                            class="ml-1 inline-flex items-center rounded-full bg-gray-100 px-1.5 py-0.5 text-xs text-gray-500"
                        >deleted</span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ a.model_default }}</td>
                    <td class="px-4 py-3 text-right text-sm text-gray-900">{{ a.total_tokens.toLocaleString() }}</td>
                    <td class="px-4 py-3 text-right text-sm text-gray-600">{{ formatCost(a.total_cost) }}</td>
                    <td class="px-4 py-3 text-right text-sm text-gray-600">{{ a.entry_count }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
