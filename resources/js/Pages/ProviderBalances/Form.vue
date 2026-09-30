<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    balance: { type: Object, default: null },
});

const isEdit = !!props.balance;
const today  = new Date().toISOString().slice(0, 10);

const form = useForm({
    provider:        props.balance?.provider         ?? '',
    balance:         props.balance?.balance          ?? '',
    currency:        props.balance?.currency         ?? 'USD',
    last_updated_at: props.balance?.last_updated_at?.slice(0, 10) ?? today,
    notes:           props.balance?.notes            ?? '',
});

function submit() {
    if (isEdit) {
        form.put(route('provider-balances.update', props.balance.id));
    } else {
        form.post(route('provider-balances.store'));
    }
}
</script>

<template>
    <AppLayout :title="isEdit ? 'Edit Balance' : 'Add Balance'">
        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center gap-3">
                <Link :href="route('provider-balances.index')" class="text-sm text-gray-500 hover:text-gray-700">← Balances</Link>
                <h1 class="text-2xl font-semibold text-gray-900">{{ isEdit ? 'Edit Balance' : 'Add Balance' }}</h1>
            </div>

            <div class="max-w-xl rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <form @submit.prevent="submit" novalidate>

                    <!-- Provider -->
                    <div class="mb-4">
                        <label for="provider" class="block text-sm font-medium text-gray-700">
                            Provider <span class="text-red-500" aria-hidden="true">*</span>
                        </label>
                        <input
                            id="provider"
                            v-model="form.provider"
                            type="text"
                            maxlength="100"
                            required
                            placeholder="e.g. OpenAI, Anthropic"
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            :class="{ 'border-red-500': form.errors.provider }"
                        />
                        <p v-if="form.errors.provider" class="mt-1 text-xs text-red-600" role="alert">{{ form.errors.provider }}</p>
                    </div>

                    <!-- Balance + Currency -->
                    <div class="mb-4 grid grid-cols-2 gap-4">
                        <div>
                            <label for="balance" class="block text-sm font-medium text-gray-700">
                                Balance <span class="text-red-500" aria-hidden="true">*</span>
                            </label>
                            <input
                                id="balance"
                                v-model="form.balance"
                                type="number"
                                min="0"
                                step="0.01"
                                required
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                :class="{ 'border-red-500': form.errors.balance }"
                            />
                            <p v-if="form.errors.balance" class="mt-1 text-xs text-red-600" role="alert">{{ form.errors.balance }}</p>
                        </div>
                        <div>
                            <label for="currency" class="block text-sm font-medium text-gray-700">
                                Currency <span class="text-red-500" aria-hidden="true">*</span>
                            </label>
                            <input
                                id="currency"
                                v-model="form.currency"
                                type="text"
                                maxlength="10"
                                required
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                :class="{ 'border-red-500': form.errors.currency }"
                            />
                            <p v-if="form.errors.currency" class="mt-1 text-xs text-red-600" role="alert">{{ form.errors.currency }}</p>
                        </div>
                    </div>

                    <!-- Last Updated At -->
                    <div class="mb-4">
                        <label for="last_updated_at" class="block text-sm font-medium text-gray-700">
                            Last Updated At <span class="text-red-500" aria-hidden="true">*</span>
                        </label>
                        <input
                            id="last_updated_at"
                            v-model="form.last_updated_at"
                            type="date"
                            required
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            :class="{ 'border-red-500': form.errors.last_updated_at }"
                        />
                        <p v-if="form.errors.last_updated_at" class="mt-1 text-xs text-red-600" role="alert">{{ form.errors.last_updated_at }}</p>
                    </div>

                    <!-- Notes -->
                    <div class="mb-6">
                        <label for="notes" class="block text-sm font-medium text-gray-700">Notes <span class="text-xs text-gray-400">optional</span></label>
                        <textarea
                            id="notes"
                            v-model="form.notes"
                            rows="3"
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        />
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-3">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
                        >
                            {{ form.processing ? 'Saving…' : 'Save' }}
                        </button>
                        <Link :href="route('provider-balances.index')" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
