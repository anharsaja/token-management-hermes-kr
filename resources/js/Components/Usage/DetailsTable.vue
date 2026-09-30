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
        <div
            class="flex flex-wrap items-end gap-3 rounded-[14px] border border-white/[0.07] p-5 backdrop-blur-[12px]"
            style="background: rgba(17,24,39,0.60)"
        >
            <!-- Agent -->
            <div>
                <label class="mb-1 block text-[11px] font-medium uppercase tracking-[0.06em] text-[#475569]">Agent</label>
                <select
                    v-model="filterAgentId"
                    class="rounded-[10px] border border-white/[0.07] bg-[#0F172A] px-3 py-2 text-[13px] text-[#F1F5F9] focus:border-[#F97316] focus:outline-none focus:ring-2 focus:ring-[#F97316]/20"
                >
                    <option value="" style="background:#0F172A">All agents</option>
                    <option v-for="a in agents" :key="a.id" :value="a.id" style="background:#0F172A">
                        {{ a.name }}{{ a.deleted_at ? ' (inactive)' : '' }}
                    </option>
                </select>
            </div>
            <!-- From -->
            <div>
                <label class="mb-1 block text-[11px] font-medium uppercase tracking-[0.06em] text-[#475569]">From</label>
                <input
                    v-model="filterDateFrom"
                    type="date"
                    class="rounded-[10px] border border-white/[0.07] bg-[#0F172A] px-3 py-2 text-[13px] text-[#F1F5F9] focus:border-[#F97316] focus:outline-none focus:ring-2 focus:ring-[#F97316]/20"
                />
            </div>
            <!-- To -->
            <div>
                <label class="mb-1 block text-[11px] font-medium uppercase tracking-[0.06em] text-[#475569]">To</label>
                <input
                    v-model="filterDateTo"
                    type="date"
                    class="rounded-[10px] border border-white/[0.07] bg-[#0F172A] px-3 py-2 text-[13px] text-[#F1F5F9] focus:border-[#F97316] focus:outline-none focus:ring-2 focus:ring-[#F97316]/20"
                />
            </div>
            <!-- Apply button — orange pill -->
            <button
                type="button"
                class="rounded-full bg-[#F97316] px-4 py-2 text-[13px] font-medium text-white transition-opacity hover:opacity-85 focus:outline-none focus:ring-2 focus:ring-[#F97316]/40"
                @click="applyFilters"
            >
                Apply
            </button>
        </div>

        <!-- Table -->
        <div
            class="overflow-hidden rounded-[14px] border border-white/[0.07]"
            style="background: rgba(17,24,39,0.60)"
        >
            <table class="min-w-full">
                <!-- Header -->
                <thead>
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.07)">
                        <th class="px-4 py-[10px] text-left text-[11px] font-medium uppercase tracking-[0.06em] text-[#475569]">
                            <button type="button" class="flex items-center gap-1 hover:text-[#94A3B8] transition-colors" @click="toggleSort('used_at')">
                                Date <span class="tabular-nums">{{ sortIcon('used_at') }}</span>
                            </button>
                        </th>
                        <th class="px-4 py-[10px] text-left text-[11px] font-medium uppercase tracking-[0.06em] text-[#475569]">Agent</th>
                        <th class="px-4 py-[10px] text-left text-[11px] font-medium uppercase tracking-[0.06em] text-[#475569]">Model</th>
                        <th class="px-4 py-[10px] text-right text-[11px] font-medium uppercase tracking-[0.06em] text-[#475569]">Input</th>
                        <th class="px-4 py-[10px] text-right text-[11px] font-medium uppercase tracking-[0.06em] text-[#475569]">Output</th>
                        <th class="px-4 py-[10px] text-right text-[11px] font-medium uppercase tracking-[0.06em] text-[#475569]">
                            <button type="button" class="ml-auto flex items-center gap-1 hover:text-[#94A3B8] transition-colors" @click="toggleSort('total_tokens')">
                                Total <span class="tabular-nums">{{ sortIcon('total_tokens') }}</span>
                            </button>
                        </th>
                        <th class="px-4 py-[10px] text-right text-[11px] font-medium uppercase tracking-[0.06em] text-[#475569]">
                            <button type="button" class="ml-auto flex items-center gap-1 hover:text-[#94A3B8] transition-colors" @click="toggleSort('cost')">
                                Cost <span class="tabular-nums">{{ sortIcon('cost') }}</span>
                            </button>
                        </th>
                        <th class="px-4 py-[10px] text-left text-[11px] font-medium uppercase tracking-[0.06em] text-[#475569]">Notes</th>
                    </tr>
                </thead>

                <!-- Body -->
                <tbody>
                    <tr v-if="!usages || usages.data.length === 0">
                        <td colspan="8" class="px-4 py-10 text-center text-[13px] text-[#475569]">
                            No usage records for this period.
                        </td>
                    </tr>
                    <tr
                        v-for="u in usages?.data ?? []"
                        :key="u.id"
                        class="transition-colors"
                        style="border-bottom: 1px solid rgba(255,255,255,0.07)"
                        onmouseenter="this.style.background='rgba(255,255,255,0.03)'"
                        onmouseleave="this.style.background=''"
                    >
                        <td class="whitespace-nowrap px-4 py-3 text-[13px] text-[#94A3B8]">{{ formatDate(u.used_at) }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-[13px] text-[#F1F5F9]">{{ u.agent?.name ?? '—' }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-[13px] text-[#94A3B8]">{{ u.model }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-[13px] tabular-nums" style="color:#F97316">{{ u.input_tokens.toLocaleString() }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-[13px] tabular-nums" style="color:#2DD4BF">{{ u.output_tokens.toLocaleString() }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-[13px] tabular-nums" style="color:#818CF8">{{ (u.input_tokens + u.output_tokens).toLocaleString() }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-[13px] tabular-nums" style="color:#FBBF24">{{ formatCost(u.cost) }}</td>
                        <td class="max-w-xs truncate px-4 py-3 text-[13px] text-[#475569]">{{ u.notes || '—' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="usages && usages.last_page > 1" class="flex items-center justify-between">
            <p class="text-[13px] text-[#475569]">
                Showing {{ usages.from }}–{{ usages.to }} of {{ usages.total }}
            </p>
            <div class="flex gap-1">
                <Link
                    v-for="link in usages.links"
                    :key="link.label"
                    :href="link.url ?? '#'"
                    v-html="link.label"
                    :class="[
                        'rounded-full px-3 py-1 text-[13px] border transition-colors',
                        link.active
                            ? 'bg-[#F97316] border-[#F97316] text-white font-medium'
                            : 'border-white/[0.07] text-[#94A3B8] hover:text-[#F1F5F9]',
                        !link.url ? 'cursor-not-allowed opacity-40' : '',
                    ]"
                    preserve-scroll
                />
            </div>
        </div>
    </div>
</template>
