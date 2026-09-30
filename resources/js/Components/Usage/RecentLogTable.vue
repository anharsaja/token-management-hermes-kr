<script setup>
const props = defineProps({
    recentLog: { type: Array, default: () => [] },
});

function relativeTime(iso) {
    if (!iso) return '—';
    const diff = Math.floor((Date.now() - new Date(iso).getTime()) / 1000);
    if (diff < 60)    return diff + 's ago';
    if (diff < 3600)  return Math.floor(diff / 60) + 'm ago';
    if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
    return Math.floor(diff / 86400) + 'd ago';
}
</script>

<template>
    <div class="custom-scrollbar flex h-full flex-col overflow-hidden rounded-[14px]"
         style="background:var(--color-surface); border:1px solid var(--color-border-subtle); box-shadow:var(--shadow-soft)">

        <div class="px-4 py-3" style="border-bottom:1px solid var(--color-border-subtle)">
            <p class="text-[11px] font-semibold uppercase tracking-wider" style="color:var(--color-text-muted)">Recent Requests</p>
        </div>

        <div class="custom-scrollbar flex-1 overflow-y-auto">
            <p v-if="recentLog.length === 0" class="py-8 text-center text-[13px]" style="color:var(--color-text-subtle)">
                No requests yet
            </p>

            <table v-else class="w-full">
                <thead>
                    <tr style="border-bottom:1px solid var(--color-border-subtle)">
                        <th class="px-4 py-2 text-left text-[11px] font-medium uppercase tracking-[0.06em]" style="color:var(--color-text-subtle)">Model</th>
                        <th class="px-4 py-2 text-right text-[11px] font-medium uppercase tracking-[0.06em]" style="color:var(--color-text-subtle)">In / Out</th>
                        <th class="px-4 py-2 text-right text-[11px] font-medium uppercase tracking-[0.06em]" style="color:var(--color-text-subtle)">When</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(entry, i) in recentLog"
                        :key="i"
                        class="transition-colors"
                        style="border-bottom:1px solid var(--color-border-subtle)"
                        onmouseenter="this.style.background='var(--color-bg-alt)'"
                        onmouseleave="this.style.background=''"
                    >
                        <td class="px-4 py-2.5 text-[13px]">
                            <div class="flex items-center gap-2">
                                <span class="h-1.5 w-1.5 flex-shrink-0 rounded-full" style="background:#22c55e"></span>
                                <span class="max-w-[100px] truncate font-medium" style="color:var(--color-text-main)">{{ entry.model }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-2.5 text-right text-[13px] tabular-nums">
                            <span style="color:#f4661f">{{ entry.input_tokens.toLocaleString() }}↑</span>
                            <span class="mx-0.5" style="color:var(--color-text-subtle)">/</span>
                            <span style="color:#16a34a">{{ entry.output_tokens.toLocaleString() }}↓</span>
                        </td>
                        <td class="px-4 py-2.5 text-right text-[12px]" style="color:var(--color-text-subtle)">
                            {{ relativeTime(entry.created_at) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
