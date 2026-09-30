<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    agents: Object,
});

const page = usePage();
const flash = computed(() => page.props.flash);

const showDeleteModal = ref(false);
const agentToDelete = ref(null);

function confirmDelete(agent) {
    agentToDelete.value = agent;
    showDeleteModal.value = true;
}

function cancelDelete() {
    agentToDelete.value = null;
    showDeleteModal.value = false;
}

function executeDelete() {
    router.delete(route('agents.destroy', agentToDelete.value.id), {
        onFinish: () => {
            showDeleteModal.value = false;
            agentToDelete.value = null;
        },
    });
}

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
}
</script>

<template>
    <AppLayout title="Agents">
        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-semibold text-gray-900">Agents</h1>
                <Link
                    :href="route('agents.create')"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Add Agent
                </Link>
            </div>

            <!-- Flash message -->
            <div
                v-if="flash"
                class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800"
                role="alert"
            >
                {{ flash }}
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Provider</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Model Default</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Created At</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr v-if="agents.data.length === 0">
                            <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500">
                                No agents yet. Add your first agent.
                            </td>
                        </tr>
                        <tr v-for="agent in agents.data" :key="agent.id" class="hover:bg-gray-50">
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">{{ agent.name }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ agent.provider }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ agent.model_default }}</td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span
                                    :class="agent.status === 'active'
                                        ? 'bg-green-100 text-green-800'
                                        : 'bg-gray-100 text-gray-600'"
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize"
                                >
                                    {{ agent.status }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ formatDate(agent.created_at) }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <Link
                                    :href="route('agents.edit', agent.id)"
                                    class="mr-3 font-medium text-indigo-600 hover:text-indigo-900"
                                >
                                    Edit
                                </Link>
                                <button
                                    type="button"
                                    class="font-medium text-red-600 hover:text-red-900"
                                    @click="confirmDelete(agent)"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="agents.last_page > 1" class="mt-4 flex items-center justify-between">
                <p class="text-sm text-gray-700">
                    Showing {{ agents.from }} to {{ agents.to }} of {{ agents.total }} agents
                </p>
                <div class="flex gap-1">
                    <Link
                        v-for="link in agents.links"
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

        <!-- Delete Confirmation Modal -->
        <div
            v-if="showDeleteModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            role="dialog"
            aria-modal="true"
            aria-labelledby="delete-modal-title"
        >
            <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
                <h2 id="delete-modal-title" class="text-lg font-semibold text-gray-900">Delete Agent</h2>
                <p class="mt-2 text-sm text-gray-600">
                    Are you sure you want to delete
                    <span class="font-medium">{{ agentToDelete?.name }}</span>?
                    This action can be undone by an admin.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <button
                        type="button"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        @click="cancelDelete"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                        @click="executeDelete"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
