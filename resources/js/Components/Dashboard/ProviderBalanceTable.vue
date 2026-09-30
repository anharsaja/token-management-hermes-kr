<script setup>
defineProps({
    balances: { type: Array, default: () => [] },
});

function formatDate(d) {
    return d ? new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '—';
}
</script>

<template>
    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-6 py-4">
            <h2 class="text-sm font-semibold text-gray-900">Provider Balance Summary</h2>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Provider</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Balance</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Currency</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Last Updated</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                <tr v-if="balances.length === 0">
                    <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">No balance records yet.</td>
                </tr>
                <tr v-for="(b, i) in balances" :key="i" class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ b.provider }}</td>
                    <td class="px-4 py-3 text-right text-sm text-gray-900">
                        {{ Number(b.balance).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ b.currency }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ formatDate(b.last_updated_at) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
