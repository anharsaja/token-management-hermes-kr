<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps({
    usage:  { type: Object, default: null },
    agents: { type: Array,  default: () => [] },
});

const isEdit = !!props.usage;
const today  = new Date().toISOString().slice(0, 10);

const form = useForm({
    agent_id:      props.usage?.agent_id      ?? '',
    model:         props.usage?.model          ?? '',
    input_tokens:  props.usage?.input_tokens   ?? '',
    output_tokens: props.usage?.output_tokens  ?? '',
    cost:          props.usage?.cost           ?? '',
    used_at:       props.usage?.used_at?.slice(0, 10) ?? today,
    notes:         props.usage?.notes          ?? '',
});

// Pre-fill model when agent is selected (AC-04)
watch(() => form.agent_id, (newId) => {
    if (!isEdit && newId) {
        const agent = props.agents.find(a => a.id == newId);
        if (agent) form.model = agent.model_default;
    }
});

function submit() {
    if (isEdit) {
        form.put(route('token-usages.update', props.usage.id));
    } else {
        form.post(route('token-usages.store'));
    }
}
</script>

<template>
    <AppLayout :title="isEdit ? 'Edit Usage' : 'Add Usage'">
        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center gap-3">
                <Link :href="route('token-usages.index')" class="text-sm text-gray-500 hover:text-gray-700">← Token Usage</Link>
                <h1 class="text-2xl font-semibold text-gray-900">{{ isEdit ? 'Edit Usage' : 'Add Usage' }}</h1>
            </div>

            <div class="max-w-xl rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <form @submit.prevent="submit" novalidate>

                    <!-- Agent -->
                    <div class="mb-4">
                        <label for="agent_id" class="block text-sm font-medium text-gray-700">
                            Agent <span class="text-red-500" aria-hidden="true">*</span>
                        </label>
                        <select
                            id="agent_id"
                            v-model="form.agent_id"
                            required
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            :class="{ 'border-red-500': form.errors.agent_id }"
                        >
                            <option value="">Select agent…</option>
                            <option v-for="a in agents" :key="a.id" :value="a.id">
                                {{ a.name }}{{ a.deleted_at ? ' (inactive)' : '' }}
                            </option>
                        </select>
                        <p v-if="form.errors.agent_id" class="mt-1 text-xs text-red-600" role="alert">{{ form.errors.agent_id }}</p>
                    </div>

                    <!-- Model -->
                    <div class="mb-4">
                        <label for="model" class="block text-sm font-medium text-gray-700">
                            Model <span class="text-red-500" aria-hidden="true">*</span>
                        </label>
                        <input
                            id="model"
                            v-model="form.model"
                            type="text"
                            maxlength="100"
                            required
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            :class="{ 'border-red-500': form.errors.model }"
                        />
                        <p v-if="form.errors.model" class="mt-1 text-xs text-red-600" role="alert">{{ form.errors.model }}</p>
                    </div>

                    <!-- Input / Output tokens -->
                    <div class="mb-4 grid grid-cols-2 gap-4">
                        <div>
                            <label for="input_tokens" class="block text-sm font-medium text-gray-700">
                                Input Tokens <span class="text-red-500" aria-hidden="true">*</span>
                            </label>
                            <input
                                id="input_tokens"
                                v-model="form.input_tokens"
                                type="number"
                                min="0"
                                step="1"
                                required
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                :class="{ 'border-red-500': form.errors.input_tokens }"
                            />
                            <p v-if="form.errors.input_tokens" class="mt-1 text-xs text-red-600" role="alert">{{ form.errors.input_tokens }}</p>
                        </div>
                        <div>
                            <label for="output_tokens" class="block text-sm font-medium text-gray-700">
                                Output Tokens <span class="text-red-500" aria-hidden="true">*</span>
                            </label>
                            <input
                                id="output_tokens"
                                v-model="form.output_tokens"
                                type="number"
                                min="0"
                                step="1"
                                required
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                :class="{ 'border-red-500': form.errors.output_tokens }"
                            />
                            <p v-if="form.errors.output_tokens" class="mt-1 text-xs text-red-600" role="alert">{{ form.errors.output_tokens }}</p>
                        </div>
                    </div>

                    <!-- Cost -->
                    <div class="mb-4">
                        <label for="cost" class="block text-sm font-medium text-gray-700">Cost (USD) <span class="text-xs text-gray-400">optional</span></label>
                        <input
                            id="cost"
                            v-model="form.cost"
                            type="number"
                            min="0"
                            step="0.000001"
                            placeholder="e.g. 0.002345"
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            :class="{ 'border-red-500': form.errors.cost }"
                        />
                        <p v-if="form.errors.cost" class="mt-1 text-xs text-red-600" role="alert">{{ form.errors.cost }}</p>
                    </div>

                    <!-- Date -->
                    <div class="mb-4">
                        <label for="used_at" class="block text-sm font-medium text-gray-700">
                            Date <span class="text-red-500" aria-hidden="true">*</span>
                        </label>
                        <input
                            id="used_at"
                            v-model="form.used_at"
                            type="date"
                            required
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            :class="{ 'border-red-500': form.errors.used_at }"
                        />
                        <p v-if="form.errors.used_at" class="mt-1 text-xs text-red-600" role="alert">{{ form.errors.used_at }}</p>
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
                        <Link :href="route('token-usages.index')" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
