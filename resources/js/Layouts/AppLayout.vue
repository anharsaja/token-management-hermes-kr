<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    title: String,
});

const sidebarOpen = ref(false);

function logout() {
    router.post(route('logout'));
}

const navItems = [
    { label: 'Dashboard', routeName: 'dashboard', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
    { label: 'Agents',    routeName: 'agents.index', icon: 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2h-2' },
];
</script>

<template>
    <div class="flex h-screen overflow-hidden bg-gray-100">
        <!-- Sidebar -->
        <aside
            class="flex w-64 flex-shrink-0 flex-col bg-gray-900"
            aria-label="Sidebar navigation"
        >
            <!-- Logo / App name -->
            <div class="flex h-16 items-center px-6">
                <span class="text-lg font-semibold text-white">Token Management</span>
            </div>

            <!-- Nav links -->
            <nav class="flex-1 space-y-1 px-3 py-4">
                <Link
                    v-for="item in navItems"
                    :key="item.routeName"
                    :href="route(item.routeName)"
                    :class="[
                        route().current(item.routeName)
                            ? 'bg-gray-800 text-white'
                            : 'text-gray-300 hover:bg-gray-700 hover:text-white',
                        'group flex items-center rounded-md px-3 py-2 text-sm font-medium',
                    ]"
                    :aria-current="route().current(item.routeName) ? 'page' : undefined"
                >
                    <svg
                        class="mr-3 h-5 w-5 flex-shrink-0"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                        aria-hidden="true"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
                    </svg>
                    {{ item.label }}
                </Link>
            </nav>

            <!-- User + logout -->
            <div class="border-t border-gray-700 p-4">
                <div class="mb-1 truncate text-sm font-medium text-white">
                    {{ $page.props.auth.user.name }}
                </div>
                <div class="mb-3 truncate text-xs text-gray-400">
                    {{ $page.props.auth.user.email }}
                </div>
                <button
                    type="button"
                    class="w-full rounded-md bg-gray-700 px-3 py-2 text-left text-sm font-medium text-gray-200 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    @click="logout"
                >
                    Log Out
                </button>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex flex-1 flex-col overflow-hidden">
            <!-- Topbar -->
            <header class="flex h-16 flex-shrink-0 items-center border-b border-gray-200 bg-white px-6">
                <h1 class="text-base font-medium text-gray-900">{{ title }}</h1>
            </header>

            <main class="flex-1 overflow-y-auto">
                <slot />
            </main>
        </div>
    </div>
</template>
