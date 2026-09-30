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
        <div class="flex flex-wrap items-end gap-3 rounded-[14px] p-4"
             style="background:var(--color-surface); border:1px solid var(--color-border-subtle); box-shadow:var(--shadow-soft)">
            <div>
                <label class="mb-1 block text-[11px] font-medium uppercase tracking-[0.06em]" style="color:var(--color-text-subtle)">Agent</label>
                <select
                    v-model="filterAgentId"
                    class="rounded-[8px] px-3 py-2 text-[13px] transition-colors focus:outline-none"
                    style="background:var(--color-surface-2); border:1px solid var(--color-border); color:var(--color-text-main)"
                >
                    <option value="" style="background:white">All agents</option>
                    <option v-for="a in agents" :key="a.id" :value="a.id" style="background:white">
                        {{ a.name }}{{ a.deleted_at ? ' (inactive)' : '' }}
                    </option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-[11px] font-medium uppercase tracking-[0.06em]" style="color:var(--color-text-subtle)">From</label>
                <input v-model="filterDateFrom" type="date"
                    class="rounded-[8px] px-3 py-2 text-[13px] transition-colors focus:outline-none"
                    style="background:var(--color-surface-2); border:1px solid var(--color-border); color:var(--color-text-main)" />
            </div>
            <div>
                <label class="mb-1 block text-[11px] font-medium uppercase tracking-[0.06em]" style="color:var(--color-text-subtle)">To</label>
                <input v-model="filterDateTo" type="date"
                    class="rounded-[8px] px-3 py-2 text-[13px] transition-colors focus:outline-none"
                    style="background:var(--color-surface-2); border:1px solid var(--color-border); color:var(--color-text-main)" />
            </div>
            <button type="button"
                class="rounded-[8px] px-4 py-2 text-[13px] font-medium text-white transition-all focus:outline-none"
                style="background:var(--color-brand-500)"
                onmouseenter="this.style.background='var(--color-brand-600)'"
                onmouseleave="this.style.background='var(--color-brand-500)'"
                @click="applyFilters">
                Apply
            </button>
        </div>

        <!-- Table -->
        <div class="overflow-hidden rounded-[14px]"
             style="background:var(--color-surface); border:1px solid var(--color-border-subtle); box-shadow:var(--shadow-soft)">
            <table class="min-w-full">
                <thead style="border-bottom:1px solid var(--color-border-subtle)">
                    <tr>
                        <th class="px-4 py-[10px] text-left text-[11px] font-medium uppercase tracking-[0.06em]" style="color:var(--color-text-subtle)">
                            <button type="button" class="flex items-center gap-1 transition-colors" style="color:inherit" onmouseenter="this.style.color='var(--color-text-main)'" onmouseleave="this.style.color='inherit'" @click="toggleSort('used_at')">
                                Date <span class="tabular-nums">{{ sortIcon('used_at') }}</span>
                            </button>
                        </th>
                        <th class="px-4 py-[10px] text-left text-[11px] font-medium uppercase tracking-[0.06em]" style="color:var(--color-text-subtle)">Agent</th>
                        <th class="px-4 py-[10px] text-left text-[11px] font-medium uppercase tracking-[0.06em]" style="color:var(--color-text-subtle)">Model</th>
                        <th class="px-4 py-[10px] text-right text-[11px] font-medium uppercase tracking-[0.06em]" style="color:var(--color-text-subtle)">Input</th>
                        <th class="px-4 py-[10px] text-right text-[11px] font-medium uppercase tracking-[0.06em]" style="color:var(--color-text-subtle)">Output</th>
                        <th class="px-4 py-[10px] text-right text-[11px] font-medium uppercase tracking-[0.06em]" style="color:var(--color-text-subtle)">
                            <button type="button" class="ml-auto flex items-center gap-1 transition-colors" style="color:inherit" onmouseenter="this.style.color='var(--color-text-main)'" onmouseleave="this.style.color='inherit'" @click="toggleSort('total_tokens')">
                                Total <span class="tabular-nums">{{ sortIcon('total_tokens') }}</span>
                            </button>
                        </th>
                        <th class="px-4 py-[10px] text-right text-[11px] font-medium uppercase tracking-[0.06em]" style="color:var(--color-text-subtle)">
                            <button type="button" class="ml-auto flex items-center gap-1 transition-colors" style="color:inherit" onmouseenter="this.style.color='var(--color-text-main)'" onmouseleave="this.style.color='inherit'" @click="toggleSort('cost')">
                                Cost <span class="tabular-nums">{{ sortIcon('cost') }}</span>
                            </button>
                        </th>
                        <th class="px-4 py-[10px] text-left text-[11px] font-medium uppercase tracking-[0.06em]" style="color:var(--color-text-subtle)">Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="!usages || usages.data.length === 0">
                        <td colspan="8" class="px-4 py-10 text-center text-[13px]" style="color:var(--color-text-subtle)">
                            No usage records for this period.
                        </td>
                    </tr>
                    <tr
                        v-for="u in usages?.data ?? []"
                        :key="u.id"
                        class="transition-colors"
                        style="border-bottom:1px solid var(--color-border-subtle)"
                        onmouseenter="this.style.background='var(--color-bg-alt)'"
                        onmouseleave="this.style.background=''"
                    >
                        <td class="whitespace-nowrap px-4 py-3 text-[13px]" style="color:var(--color-text-muted)">{{ formatDate(u.used_at) }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-[13px] font-medium" style="color:var(--color-text-main)">{{ u.agent?.name ?? '—' }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-[13px]" style="color:var(--color-text-muted)">{{ u.model }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-[13px] tabular-nums" style="color:#f4661f">{{ u.input_tokens.toLocaleString() }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-[13px] tabular-nums" style="color:#16a34a">{{ u.output_tokens.toLocaleString() }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-[13px] tabular-nums" style="color:#2563eb">{{ (u.input_tokens + u.output_tokens).toLocaleString() }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-[13px] tabular-nums" style="color:#f59e0b">{{ formatCost(u.cost) }}</td>
                        <td class="max-w-xs truncate px-4 py-3 text-[13px]" style="color:var(--color-text-subtle)">{{ u.notes || '—' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="usages && usages.last_page > 1" class="flex items-center justify-between">
            <p class="text-[13px]" style="color:var(--color-text-subtle)">
                Showing {{ usages.from }}–{{ usages.to }} of {{ usages.total }}
            </p>
            <div class="flex gap-1">
                <Link
                    v-for="link in usages.links"
                    :key="link.label"
                    :href="link.url ?? '#'"
                    v-html="link.label"
                    :class="['rounded-[8px] px-3 py-1 text-[13px] transition-colors', !link.url ? 'cursor-not-allowed opacity-40' : '']"
                    :style="link.active
                        ? 'background:var(--color-brand-500); color:white; font-weight:500'
                        : 'background:var(--color-surface-2); color:var(--color-text-muted)'"
                    preserve-scroll
                />
            </div>
        </div>
    </div>
</template>
