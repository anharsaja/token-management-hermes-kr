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
    <div
        class="rounded-[14px] border border-white/[0.07] p-5 backdrop-blur-[12px]"
        style="background: rgba(17,24,39,0.60)"
    >
        <p class="mb-4 text-[11px] font-medium uppercase tracking-[0.08em] text-[#94A3B8]">Recent Log</p>

        <div class="max-h-80 overflow-y-auto">
            <p v-if="recentLog.length === 0" class="py-8 text-center text-[13px] text-[#475569]">
                No usage recorded yet
            </p>

            <table v-else class="w-full">
                <thead>
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.07)">
                        <th class="pb-2 text-left text-[11px] font-medium uppercase tracking-[0.06em] text-[#475569]">Model</th>
                        <th class="pb-2 text-right text-[11px] font-medium uppercase tracking-[0.06em] text-[#475569]">In / Out</th>
                        <th class="pb-2 text-right text-[11px] font-medium uppercase tracking-[0.06em] text-[#475569]">When</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(entry, i) in recentLog"
                        :key="i"
                        class="transition-colors"
                        style="border-bottom: 1px solid rgba(255,255,255,0.07)"
                        onmouseenter="this.style.background='rgba(255,255,255,0.03)'"
                        onmouseleave="this.style.background=''"
                    >
                        <td class="py-2.5 pr-2 text-[13px] text-[#F1F5F9] max-w-[100px] truncate">{{ entry.model }}</td>
                        <td class="py-2.5 text-right text-[13px] tabular-nums">
                            <span style="color:#F97316">{{ entry.input_tokens.toLocaleString() }}</span>
                            <span style="color:#334155"> / </span>
                            <span style="color:#2DD4BF">{{ entry.output_tokens.toLocaleString() }}</span>
                        </td>
                        <td class="py-2.5 pl-2 text-right text-[12px]" style="color:#475569">
                            {{ relativeTime(entry.created_at) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
