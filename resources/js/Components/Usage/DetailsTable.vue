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
const sortBy         = ref(props.filters?.sort_by   ?? 'used_at');
const sortDir        = ref(props.filters?.sort_dir  ?? 'desc');

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
        <div class="flex flex-wrap items-end gap-3 rounded-[12px] border border-gray-100 bg-white p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)]">
            <div>
                <label class="mb-1 block text-[11px] font-medium uppercase tracking-[0.06em] text-gray-400">Agent</label>
                <select
                    v-model="filterAgentId"
                    class="rounded-[8px] border border-gray-200 bg-white px-3 py-2 text-[13px] text-gray-700 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-400/20"
                >
                    <option value="">All agents</option>
                    <option v-for="a in agents" :key="a.id" :value="a.id">
                        {{ a.name }}{{ a.deleted_at ? ' (inactive)' : '' }}
                    </option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-[11px] font-medium uppercase tracking-[0.06em] text-gray-400">From</label>
                <input v-model="filterDateFrom" type="date"
                    class="rounded-[8px] border border-gray-200 bg-white px-3 py-2 text-[13px] text-gray-700 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-400/20" />
            </div>
            <div>
                <label class="mb-1 block text-[11px] font-medium uppercase tracking-[0.06em] text-gray-400">To</label>
                <input v-model="filterDateTo" type="date"
                    class="rounded-[8px] border border-gray-200 bg-white px-3 py-2 text-[13px] text-gray-700 focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-400/20" />
            </div>
            <button type="button"
                class="rounded-full bg-orange-500 px-4 py-2 text-[13px] font-medium text-white transition-opacity hover:opacity-85 focus:outline-none focus:ring-2 focus:ring-orange-400/40"
                @click="applyFilters">
                Apply
            </button>
        </div>

        <!-- Table -->
        <div class="overflow-hidden rounded-[12px] border border-gray-100 bg-white shadow-[0_1px_3px_rgba(0,0,0,0.06)]">
            <table class="min-w-full">
                <thead class="border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-[10px] text-left text-[11px] font-medium uppercase tracking-[0.06em] text-gray-400">
                            <button type="button" class="flex items-center gap-1 transition-colors hover:text-gray-600" @click="toggleSort('used_at')">
                                Date <span class="tabular-nums">{{ sortIcon('used_at') }}</span>
                            </button>
                        </th>
                        <th class="px-4 py-[10px] text-left text-[11px] font-medium uppercase tracking-[0.06em] text-gray-400">Agent</th>
                        <th class="px-4 py-[10px] text-left text-[11px] font-medium uppercase tracking-[0.06em] text-gray-400">Model</th>
                        <th class="px-4 py-[10px] text-right text-[11px] font-medium uppercase tracking-[0.06em] text-gray-400">Input</th>
                        <th class="px-4 py-[10px] text-right text-[11px] font-medium uppercase tracking-[0.06em] text-gray-400">Output</th>
                        <th class="px-4 py-[10px] text-right text-[11px] font-medium uppercase tracking-[0.06em] text-gray-400">
                            <button type="button" class="ml-auto flex items-center gap-1 transition-colors hover:text-gray-600" @click="toggleSort('total_tokens')">
                                Total <span class="tabular-nums">{{ sortIcon('total_tokens') }}</span>
                            </button>
                        </th>
                        <th class="px-4 py-[10px] text-right text-[11px] font-medium uppercase tracking-[0.06em] text-gray-400">
                            <button type="button" class="ml-auto flex items-center gap-1 transition-colors hover:text-gray-600" @click="toggleSort('cost')">
                                Cost <span class="tabular-nums">{{ sortIcon('cost') }}</span>
                            </button>
                        </th>
                        <th class="px-4 py-[10px] text-left text-[11px] font-medium uppercase tracking-[0.06em] text-gray-400">Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <tr v-if="!usages || usages.data.length === 0">
                        <td colspan="8" class="px-4 py-10 text-center text-[13px] text-gray-400">
                            No usage records for this period.
                        </td>
                    </tr>
                    <tr v-for="u in usages?.data ?? []" :key="u.id" class="transition-colors hover:bg-gray-50/60">
                        <td class="whitespace-nowrap px-4 py-3 text-[13px] text-gray-500">{{ formatDate(u.used_at) }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-[13px] font-medium text-gray-800">{{ u.agent?.name ?? '—' }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-[13px] text-gray-500">{{ u.model }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-[13px] tabular-nums text-orange-500">{{ u.input_tokens.toLocaleString() }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-[13px] tabular-nums text-emerald-500">{{ u.output_tokens.toLocaleString() }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-[13px] tabular-nums text-blue-500">{{ (u.input_tokens + u.output_tokens).toLocaleString() }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-[13px] tabular-nums text-amber-500">{{ formatCost(u.cost) }}</td>
                        <td class="max-w-xs truncate px-4 py-3 text-[13px] text-gray-400">{{ u.notes || '—' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="usages && usages.last_page > 1" class="flex items-center justify-between">
            <p class="text-[13px] text-gray-400">Showing {{ usages.from }}–{{ usages.to }} of {{ usages.total }}</p>
            <div class="flex gap-1">
                <Link
                    v-for="link in usages.links"
                    :key="link.label"
                    :href="link.url ?? '#'"
                    v-html="link.label"
                    :class="[
                        'rounded-full border px-3 py-1 text-[13px] transition-colors',
                        link.active
                            ? 'border-orange-500 bg-orange-500 font-medium text-white'
                            : 'border-gray-200 text-gray-500 hover:border-gray-300 hover:text-gray-700',
                        !link.url ? 'cursor-not-allowed opacity-40' : '',
                    ]"
                    preserve-scroll
                />
            </div>
        </div>
    </div>
</template>
