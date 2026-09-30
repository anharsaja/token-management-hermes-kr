<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    balances: Object,
});

const page  = usePage();
const flash = computed(() => page.props.flash);

const showDeleteModal   = ref(false);
const balanceToDelete   = ref(null);

function confirmDelete(b) {
    balanceToDelete.value  = b;
    showDeleteModal.value   = true;
}
function cancelDelete() {
    balanceToDelete.value  = null;
    showDeleteModal.value   = false;
}
function executeDelete() {
    router.delete(route('provider-balances.destroy', balanceToDelete.value.id), {
        onFinish: () => { showDeleteModal.value = false; balanceToDelete.value = null; },
    });
}

function formatDate(d) {
    return d ? new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '—';
}
</script>

<template>
    <AppLayout title="Provider Balances">
        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-semibold text-gray-900">Provider Balances</h1>
                <div class="flex items-center gap-2">
                    <a
                        :href="route('provider-balances.export')"
                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Export CSV
                    </a>
                    <Link
                        :href="route('provider-balances.create')"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Add Balance
                    </Link>
                </div>
            </div>

            <!-- Flash -->
            <div v-if="flash" class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800" role="alert">
                {{ flash }}
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Provider</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Balance</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Currency</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Last Updated</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Notes</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr v-if="balances.data.length === 0">
                            <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500">No balances recorded yet.</td>
                        </tr>
                        <tr v-for="b in balances.data" :key="b.id" class="hover:bg-gray-50">
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">{{ b.provider }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-900">
                                {{ Number(b.balance).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ b.currency }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ formatDate(b.last_updated_at) }}</td>
                            <td class="max-w-xs truncate px-6 py-4 text-sm text-gray-600">{{ b.notes || '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <Link :href="route('provider-balances.edit', b.id)" class="mr-3 font-medium text-indigo-600 hover:text-indigo-900">Edit</Link>
                                <button type="button" class="font-medium text-red-600 hover:text-red-900" @click="confirmDelete(b)">Delete</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="balances.last_page > 1" class="mt-4 flex items-center justify-between">
                <p class="text-sm text-gray-700">
                    Showing {{ balances.from }} to {{ balances.to }} of {{ balances.total }} entries
                </p>
                <div class="flex gap-1">
                    <Link
                        v-for="link in balances.links"
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
                <h2 class="text-lg font-semibold text-gray-900">Delete Balance Entry</h2>
                <p class="mt-2 text-sm text-gray-600">
                    Are you sure you want to delete the balance entry for
                    <span class="font-medium">{{ balanceToDelete?.provider }}</span>?
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" @click="cancelDelete">Cancel</button>
                    <button type="button" class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700" @click="executeDelete">Delete</button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
