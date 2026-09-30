<script setup>
import { Link, router } from '@inertiajs/vue3';

defineProps({
    title: String,
});

function logout() {
    router.post(route('logout'));
}

const navItems = [
    { label: 'Dashboard',         routeName: 'dashboard',               icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
    { label: 'Agents',            routeName: 'agents.index',            icon: 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2h-2' },
    { label: 'Token Usage',       routeName: 'token-usages.index',      icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' },
    { label: 'Balances',          routeName: 'provider-balances.index', icon: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z' },
    { label: 'Usage & Analytics', routeName: 'usage.index',             icon: 'M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z' },
];
</script>

<template>
    <div class="flex h-screen overflow-hidden" style="background:#F8F9FB">

        <!-- Sidebar — light, 260px, border-r -->
        <aside
            class="flex w-[260px] flex-shrink-0 flex-col border-r border-gray-200 bg-white"
            aria-label="Sidebar navigation"
        >
            <!-- Logo -->
            <div class="flex h-[60px] items-center gap-3 border-b border-gray-100 px-4">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-500">
                    <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900">Token Management</p>
                </div>
            </div>

            <!-- Nav -->
            <nav class="flex-1 space-y-0.5 px-3 py-3">
                <Link
                    v-for="item in navItems"
                    :key="item.routeName"
                    :href="route(item.routeName)"
                    :class="[
                        'group flex h-10 items-center rounded-[8px] px-3 text-[13px] font-medium transition-colors',
                        route().current(item.routeName)
                            ? 'bg-orange-50 text-orange-600'
                            : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900',
                    ]"
                    :style="route().current(item.routeName)
                        ? 'border-left: 2px solid #f97316'
                        : ''"
                    :aria-current="route().current(item.routeName) ? 'page' : undefined"
                >
                    <svg
                        class="mr-2.5 h-4 w-4 flex-shrink-0"
                        :class="route().current(item.routeName) ? 'text-orange-500' : 'text-gray-400 group-hover:text-gray-500'"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2"
                        aria-hidden="true"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
                    </svg>
                    {{ item.label }}
                </Link>
            </nav>

            <!-- System section label -->
            <div class="px-4 pb-1 pt-2">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">System</p>
            </div>
            <div class="pb-2 px-3">
                <div class="flex h-10 items-center rounded-[8px] px-3 text-[13px] text-gray-400">
                    <svg class="mr-2.5 h-4 w-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Settings
                </div>
            </div>

            <!-- User + logout -->
            <div class="border-t border-gray-100 p-4">
                <div class="mb-0.5 truncate text-[13px] font-medium text-gray-900">
                    {{ $page.props.auth.user.name }}
                </div>
                <div class="mb-3 truncate text-[12px] text-gray-400">
                    {{ $page.props.auth.user.email }}
                </div>
                <button
                    type="button"
                    class="w-full rounded-[8px] border border-gray-200 bg-white px-3 py-1.5 text-left text-[13px] text-gray-600 transition-colors hover:bg-gray-50 hover:text-gray-900 focus:outline-none"
                    @click="logout"
                >
                    Log Out
                </button>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex flex-1 flex-col overflow-hidden">
            <!-- Topbar -->
            <header class="flex h-[60px] flex-shrink-0 items-center border-b border-gray-200 bg-white px-6">
                <h1 class="text-[13px] font-medium text-gray-600">{{ title }}</h1>
            </header>

            <main class="flex-1 overflow-y-auto" style="background:#F8F9FB">
                <slot />
            </main>
        </div>
    </div>
</template>
