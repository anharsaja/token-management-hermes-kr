<script setup>
import { computed } from 'vue';

const props = defineProps({
    recentLog: { type: Array, default: () => [] },
});

function relativeTime(iso) {
    if (!iso) return '—';
    const diff = Math.floor((Date.now() - new Date(iso).getTime()) / 1000);
    if (diff < 60)   return diff + 's ago';
    if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
    if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
    return Math.floor(diff / 86400) + 'd ago';
}
</script>

<template>
    <div class="rounded-xl border border-gray-700 bg-gray-800/60 p-4 backdrop-blur">
        <p class="mb-3 text-xs font-semibold uppercase tracking-widest text-gray-400">Recent Log</p>
        <div class="max-h-80 overflow-y-auto">
            <div v-if="recentLog.length === 0" class="py-8 text-center text-sm text-gray-500">
                No usage recorded yet
            </div>
            <table v-else class="w-full">
                <thead>
                    <tr class="text-xs text-gray-500">
                        <th class="pb-2 text-left font-medium">Model</th>
                        <th class="pb-2 text-right font-medium">In / Out</th>
                        <th class="pb-2 text-right font-medium">When</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700/50">
                    <tr v-for="(entry, i) in recentLog" :key="i" class="text-xs">
                        <td class="py-1.5 pr-2 font-medium text-gray-200 max-w-[100px] truncate">{{ entry.model }}</td>
                        <td class="py-1.5 text-right tabular-nums">
                            <span class="text-orange-400">{{ entry.input_tokens.toLocaleString() }}</span>
                            <span class="text-gray-600"> / </span>
                            <span class="text-teal-400">{{ entry.output_tokens.toLocaleString() }}</span>
                        </td>
                        <td class="py-1.5 pl-2 text-right text-gray-500">{{ relativeTime(entry.created_at) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
