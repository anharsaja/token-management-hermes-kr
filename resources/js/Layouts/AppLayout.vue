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
    <div class="flex h-screen overflow-hidden" style="background:var(--color-bg)">

        <!-- Sidebar — vibrancy frosted glass, 288px, matches 9Router w-72 -->
        <aside
            class="bg-vibrancy custom-scrollbar flex w-72 flex-shrink-0 flex-col"
            style="border-right:1px solid var(--color-border-subtle); min-height:100%"
            aria-label="Sidebar navigation"
        >
            <!-- macOS traffic lights -->
            <div class="flex items-center gap-2 px-6 pb-2 pt-5">
                <div class="h-3 w-3 rounded-full" style="background:#FF5F56"></div>
                <div class="h-3 w-3 rounded-full" style="background:#FFBD2E"></div>
                <div class="h-3 w-3 rounded-full" style="background:#27C93F"></div>
            </div>

            <!-- Brand -->
            <div class="flex flex-col gap-2 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-[10px]"
                         style="background:linear-gradient(135deg,#e56a4a,#a64027); box-shadow:var(--shadow-warm)">
                        <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-[17px] font-semibold tracking-tight" style="color:var(--color-text-main)">Token Mgmt</p>
                    </div>
                </div>
            </div>

            <!-- Nav links -->
            <nav class="custom-scrollbar flex-1 space-y-0.5 overflow-y-auto px-4 py-2">
                <Link
                    v-for="item in navItems"
                    :key="item.routeName"
                    :href="route(item.routeName)"
                    :class="[
                        'group flex items-center gap-3 rounded-lg px-3 py-[6px] text-[13px] font-medium transition-all',
                        route().current(item.routeName)
                            ? 'text-[var(--color-brand-500)]'
                            : 'hover:text-[var(--color-text-main)]',
                    ]"
                    :style="route().current(item.routeName)
                        ? 'background:rgba(229,106,74,0.10); color:var(--color-brand-500)'
                        : 'color:var(--color-text-muted)'"
                    :aria-current="route().current(item.routeName) ? 'page' : undefined"
                >
                    <svg
                        class="h-[18px] w-[18px] flex-shrink-0 transition-colors"
                        :style="route().current(item.routeName) ? 'color:var(--color-brand-500)' : 'color:#6b7280'"
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

            <!-- System section -->
            <div class="px-4 pb-1 pt-3">
                <p class="px-3 text-[11px] font-semibold uppercase tracking-wider" style="color:var(--color-text-subtle)">System</p>
            </div>
            <div class="px-4 pb-2">
                <div class="flex items-center gap-3 rounded-lg px-3 py-[6px] text-[13px] font-medium" style="color:var(--color-text-muted)">
                    <svg class="h-[18px] w-[18px] flex-shrink-0" style="color:#9ca3af" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Settings
                </div>
            </div>

            <!-- User + logout -->
            <div class="p-4" style="border-top:1px solid var(--color-border-subtle)">
                <div class="mb-0.5 truncate text-[13px] font-semibold" style="color:var(--color-text-main)">
                    {{ $page.props.auth.user.name }}
                </div>
                <div class="mb-3 truncate text-[12px]" style="color:var(--color-text-muted)">
                    {{ $page.props.auth.user.email }}
                </div>
                <button
                    type="button"
                    class="w-full rounded-[8px] px-3 py-1.5 text-left text-[13px] font-medium transition-colors focus:outline-none"
                    style="background:var(--color-surface-2); color:var(--color-text-muted); border:1px solid var(--color-border-subtle)"
                    onmouseenter="this.style.background='var(--color-surface-3)'; this.style.color='var(--color-text-main)'"
                    onmouseleave="this.style.background='var(--color-surface-2)'; this.style.color='var(--color-text-muted)'"
                    @click="logout"
                >
                    Log Out
                </button>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex flex-1 flex-col overflow-hidden">
            <main class="custom-scrollbar flex-1 overflow-y-auto" style="background:var(--color-bg)">
                <slot />
            </main>
        </div>
    </div>
</template>
