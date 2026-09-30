<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    agent: {
        type: Object,
        default: null,
    },
});

const isEdit = !!props.agent;

const form = useForm({
    name:          props.agent?.name          ?? '',
    provider:      props.agent?.provider      ?? '',
    model_default: props.agent?.model_default ?? '',
    description:   props.agent?.description   ?? '',
    status:        props.agent?.status        ?? 'active',
});

function submit() {
    if (isEdit) {
        form.put(route('agents.update', props.agent.id));
    } else {
        form.post(route('agents.store'));
    }
}
</script>

<template>
    <AppLayout :title="isEdit ? 'Edit Agent' : 'Add Agent'">
        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center gap-3">
                <Link
                    :href="route('agents.index')"
                    class="text-sm text-gray-500 hover:text-gray-700"
                >
                    ← Agents
                </Link>
                <h1 class="text-2xl font-semibold text-gray-900">
                    {{ isEdit ? 'Edit Agent' : 'Add Agent' }}
                </h1>
            </div>

            <div class="max-w-xl rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <form @submit.prevent="submit" novalidate>
                    <!-- Name -->
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700">
                            Name <span class="text-red-500" aria-hidden="true">*</span>
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            maxlength="100"
                            required
                            autocomplete="off"
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            :class="{ 'border-red-500': form.errors.name }"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-red-600" role="alert">
                            {{ form.errors.name }}
                        </p>
                    </div>

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
                            autocomplete="off"
                            placeholder="e.g. OpenAI, Anthropic"
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            :class="{ 'border-red-500': form.errors.provider }"
                        />
                        <p v-if="form.errors.provider" class="mt-1 text-xs text-red-600" role="alert">
                            {{ form.errors.provider }}
                        </p>
                    </div>

                    <!-- Model Default -->
                    <div class="mb-4">
                        <label for="model_default" class="block text-sm font-medium text-gray-700">
                            Model Default <span class="text-red-500" aria-hidden="true">*</span>
                        </label>
                        <input
                            id="model_default"
                            v-model="form.model_default"
                            type="text"
                            maxlength="100"
                            required
                            autocomplete="off"
                            placeholder="e.g. gpt-4o, claude-3-5-sonnet"
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            :class="{ 'border-red-500': form.errors.model_default }"
                        />
                        <p v-if="form.errors.model_default" class="mt-1 text-xs text-red-600" role="alert">
                            {{ form.errors.model_default }}
                        </p>
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-gray-700">
                            Description
                        </label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="3"
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            :class="{ 'border-red-500': form.errors.description }"
                        />
                        <p v-if="form.errors.description" class="mt-1 text-xs text-red-600" role="alert">
                            {{ form.errors.description }}
                        </p>
                    </div>

                    <!-- Status -->
                    <div class="mb-6">
                        <span class="block text-sm font-medium text-gray-700">Status</span>
                        <div class="mt-2 flex items-center gap-2">
                            <button
                                type="button"
                                role="switch"
                                :aria-checked="form.status === 'active'"
                                :class="form.status === 'active' ? 'bg-indigo-600' : 'bg-gray-200'"
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                @click="form.status = form.status === 'active' ? 'inactive' : 'active'"
                            >
                                <span
                                    :class="form.status === 'active' ? 'translate-x-5' : 'translate-x-0'"
                                    class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                />
                            </button>
                            <span class="text-sm text-gray-700 capitalize">{{ form.status }}</span>
                        </div>
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
                        <Link
                            :href="route('agents.index')"
                            class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Cancel
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
