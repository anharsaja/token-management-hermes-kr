<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    usages:  Object,
    agents:  Array,
    filters: Object,
});

const page = usePage();
const flash = computed(() => page.props.flash);

// Filter state — initialise from server-side filters (URL params)
const filterAgentId = ref(props.filters?.agent_id ?? '');
const filterDateFrom = ref(props.filters?.date_from ?? '');
const filterDateTo   = ref(props.filters?.date_to ?? '');

function applyFilters() {
    router.get(route('token-usages.index'), {
        agent_id:  filterAgentId.value  || undefined,
        date_from: filterDateFrom.value || undefined,
        date_to:   filterDateTo.value   || undefined,
    }, { preserveState: true, replace: true });
}

function clearFilters() {
    filterAgentId.value  = '';
    filterDateFrom.value = '';
    filterDateTo.value   = '';
    applyFilters();
}

// Delete modal
const showDeleteModal = ref(false);
const usageToDelete   = ref(null);

function confirmDelete(usage) {
    usageToDelete.value  = usage;
    showDeleteModal.value = true;
}

function cancelDelete() {
    usageToDelete.value  = null;
    showDeleteModal.value = false;
}

function executeDelete() {
    router.delete(route('token-usages.destroy', usageToDelete.value.id), {
        onFinish: () => { showDeleteModal.value = false; usageToDelete.value = null; },
    });
}

function formatDate(d) {
    return d ? new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '—';
}

function formatCost(c) {
    if (c === null || c === undefined || c === '') return '—';
    return '$' + Number(c).toFixed(6).replace(/\.?0+$/, '');
}
</script>

<template>
    <AppLayout title="Token Usage">
        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-semibold text-gray-900">Token Usage</h1>
                <div class="flex items-center gap-2">
                    <a
                        :href="route('token-usages.export', {
                            agent_id:  filterAgentId  || undefined,
                            date_from: filterDateFrom || undefined,
                            date_to:   filterDateTo   || undefined,
                        })"
                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Export CSV
                    </a>
                    <Link
                        :href="route('token-usages.create')"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Add Usage
                    </Link>
                </div>
            </div>

            <!-- Flash -->
            <div v-if="flash" class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800" role="alert">
                {{ flash }}
            </div>

            <!-- Filter bar -->
            <div class="mb-4 flex flex-wrap items-end gap-3 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Agent</label>
                    <select
                        v-model="filterAgentId"
                        class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    >
                        <option value="">All agents</option>
                        <option v-for="a in agents" :key="a.id" :value="a.id">
                            {{ a.name }}{{ a.deleted_at ? ' (inactive)' : '' }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">From</label>
                    <input
                        v-model="filterDateFrom"
                        type="date"
                        class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">To</label>
                    <input
                        v-model="filterDateTo"
                        type="date"
                        class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    />
                </div>
                <button
                    type="button"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                    @click="applyFilters"
                >
                    Filter
                </button>
                <button
                    type="button"
                    class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    @click="clearFilters"
                >
                    Clear
                </button>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Agent</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Model</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Input</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Output</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Cost</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Notes</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr v-if="usages.data.length === 0">
                            <td colspan="8" class="px-4 py-10 text-center text-sm text-gray-500">
                                No usage records yet.
                            </td>
                        </tr>
                        <tr v-for="u in usages.data" :key="u.id" class="hover:bg-gray-50">
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600">{{ formatDate(u.used_at) }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900">
                                {{ u.agent ? u.agent.name : '—' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600">{{ u.model }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-600">{{ u.input_tokens.toLocaleString() }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-600">{{ u.output_tokens.toLocaleString() }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-600">{{ formatCost(u.cost) }}</td>
                            <td class="max-w-xs truncate px-4 py-3 text-sm text-gray-600">{{ u.notes || '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm">
                                <Link :href="route('token-usages.edit', u.id)" class="mr-3 font-medium text-indigo-600 hover:text-indigo-900">Edit</Link>
                                <button type="button" class="font-medium text-red-600 hover:text-red-900" @click="confirmDelete(u)">Delete</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="usages.last_page > 1" class="mt-4 flex items-center justify-between">
                <p class="text-sm text-gray-700">
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
                            link.active ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100',
                            !link.url ? 'cursor-not-allowed opacity-50' : '',
                        ]"
                        preserve-scroll
                    />
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" role="dialog" aria-modal="true">
            <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
                <h2 class="text-lg font-semibold text-gray-900">Delete Usage Entry</h2>
                <p class="mt-2 text-sm text-gray-600">Are you sure you want to delete this entry?</p>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" @click="cancelDelete">Cancel</button>
                    <button type="button" class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700" @click="executeDelete">Delete</button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
