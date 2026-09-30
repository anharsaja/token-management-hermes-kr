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
    <div class="flex h-full flex-col rounded-[12px] border border-gray-100 bg-white shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
        <div class="border-b border-gray-100 px-4 py-3">
            <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-gray-400">Recent Requests</p>
        </div>

        <div class="flex-1 overflow-y-auto">
            <p v-if="recentLog.length === 0" class="py-8 text-center text-[13px] text-gray-400">
                No requests yet
            </p>

            <table v-else class="w-full">
                <thead>
                    <tr class="border-b border-gray-50">
                        <th class="px-4 py-2 text-left text-[11px] font-medium uppercase tracking-[0.06em] text-gray-400">Model</th>
                        <th class="px-4 py-2 text-right text-[11px] font-medium uppercase tracking-[0.06em] text-gray-400">In / Out</th>
                        <th class="px-4 py-2 text-right text-[11px] font-medium uppercase tracking-[0.06em] text-gray-400">When</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <tr
                        v-for="(entry, i) in recentLog"
                        :key="i"
                        class="transition-colors hover:bg-gray-50/60"
                    >
                        <td class="px-4 py-2.5 text-[13px]">
                            <div class="flex items-center gap-2">
                                <span class="h-1.5 w-1.5 flex-shrink-0 rounded-full bg-emerald-400"></span>
                                <span class="max-w-[110px] truncate font-medium text-gray-700">{{ entry.model }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-2.5 text-right text-[13px] tabular-nums">
                            <span class="text-orange-500">{{ entry.input_tokens.toLocaleString() }}↑</span>
                            <span class="mx-1 text-gray-300">/</span>
                            <span class="text-emerald-500">{{ entry.output_tokens.toLocaleString() }}↓</span>
                        </td>
                        <td class="px-4 py-2.5 text-right text-[12px] text-gray-400">{{ relativeTime(entry.created_at) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
