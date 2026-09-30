<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    usages:  { type: Object, default: null },
    agents:  { type: Array,  default: () => [] },
    filters: { type: Object, default: () => ({}) },
    range:   { type: String, default: '7d' },
});

const filterAgentId  = ref(props.filters?.agent_id  ?? '');
const filterDateFrom = ref(props.filters?.date_from ?? '');
const filterDateTo   = ref(props.filters?.date_to   ?? '');
const sortBy  = ref(props.filters?.sort_by  ?? 'used_at');
const sortDir = ref(props.filters?.sort_dir ?? 'desc');

function applyFilters() {
    router.get(route('usage.index'), {
        range:     props.range,
        tab:       'details',
        agent_id:  filterAgentId.value  || undefined,
        date_from: filterDateFrom.value || undefined,
        date_to:   filterDateTo.value   || undefined,
        sort_by:   sortBy.value,
        sort_dir:  sortDir.value,
    }, { preserveState: true, replace: true });
}

function toggleSort(col) {
    if (sortBy.value === col) {
        sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value  = col;
        sortDir.value = 'desc';
    }
    applyFilters();
}

function sortIcon(col) {
    if (sortBy.value !== col) return '⇅';
    return sortDir.value === 'asc' ? '↑' : '↓';
}

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
    <div class="space-y-4">
        <!-- Filter bar -->
        <div class="flex flex-wrap items-end gap-3 rounded-xl border border-gray-700 bg-gray-800/60 p-4">
            <div>
                <label class="block text-xs text-gray-400 mb-1">Agent</label>
                <select
                    v-model="filterAgentId"
                    class="rounded-md border border-gray-600 bg-gray-900 px-3 py-2 text-sm text-gray-200 focus:border-indigo-500 focus:outline-none"
                >
                    <option value="">All agents</option>
                    <option v-for="a in agents" :key="a.id" :value="a.id">
                        {{ a.name }}{{ a.deleted_at ? ' (inactive)' : '' }}
                    </option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">From</label>
                <input v-model="filterDateFrom" type="date" class="rounded-md border border-gray-600 bg-gray-900 px-3 py-2 text-sm text-gray-200 focus:border-indigo-500 focus:outline-none" />
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">To</label>
                <input v-model="filterDateTo" type="date" class="rounded-md border border-gray-600 bg-gray-900 px-3 py-2 text-sm text-gray-200 focus:border-indigo-500 focus:outline-none" />
            </div>
            <button type="button" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700" @click="applyFilters">
                Apply
            </button>
        </div>

        <!-- Table -->
        <div class="overflow-hidden rounded-xl border border-gray-700 bg-gray-800/60">
            <table class="min-w-full divide-y divide-gray-700">
                <thead class="bg-gray-900/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">
                            <button type="button" class="flex items-center gap-1 hover:text-white" @click="toggleSort('used_at')">
                                Date <span>{{ sortIcon('used_at') }}</span>
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Agent</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Model</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-400">Input</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-400">Output</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-400">
                            <button type="button" class="flex items-center gap-1 hover:text-white ml-auto" @click="toggleSort('total_tokens')">
                                Total <span>{{ sortIcon('total_tokens') }}</span>
                            </button>
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-400">
                            <button type="button" class="flex items-center gap-1 hover:text-white ml-auto" @click="toggleSort('cost')">
                                Cost <span>{{ sortIcon('cost') }}</span>
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700/50">
                    <tr v-if="!usages || usages.data.length === 0">
                        <td colspan="8" class="px-4 py-10 text-center text-sm text-gray-500">No usage records for this period.</td>
                    </tr>
                    <tr v-for="u in usages?.data ?? []" :key="u.id" class="hover:bg-gray-700/30">
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-400">{{ formatDate(u.used_at) }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-200">{{ u.agent?.name ?? '—' }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-400">{{ u.model }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-orange-400">{{ u.input_tokens.toLocaleString() }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-teal-400">{{ u.output_tokens.toLocaleString() }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-indigo-300">{{ (u.input_tokens + u.output_tokens).toLocaleString() }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-400">{{ formatCost(u.cost) }}</td>
                        <td class="max-w-xs truncate px-4 py-3 text-sm text-gray-500">{{ u.notes || '—' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="usages && usages.last_page > 1" class="flex items-center justify-between">
            <p class="text-sm text-gray-500">
                Showing {{ usages.from }} to {{ usages.to }} of {{ usages.total }} entries
            </p>
            <div class="flex gap-1">
                <Link
                    v-for="link in usages.links"
                    :key="link.label"
                    :href="link.url ?? '#'"
                    v-html="link.label"
                    :class="[
                        'rounded px-3 py-1 text-sm',
                        link.active ? 'bg-indigo-600 text-white' : 'bg-gray-800 text-gray-400 hover:bg-gray-700',
                        !link.url ? 'cursor-not-allowed opacity-40' : '',
                    ]"
                    preserve-scroll
                />
            </div>
        </div>
    </div>
</template>
