<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    nodes: { type: Array, default: () => [] },
});

const emit = defineEmits(['select-agent']);
const selectedId = ref(null);

const WIDTH  = 540;
const HEIGHT = 360;
const CX     = WIDTH  / 2;
const CY     = HEIGHT / 2;
const R      = 145;

const maxTokens = computed(() => Math.max(1, ...props.nodes.map(n => n.total_tokens)));

function nodeRadius(tokens) {
    const min = 16, max = 36;
    if (tokens === 0) return min;
    return min + ((tokens / maxTokens.value) * (max - min));
}

const positioned = computed(() => {
    const count = props.nodes.length;
    return props.nodes.map((n, i) => {
        const angle = (2 * Math.PI * i) / Math.max(count, 1) - Math.PI / 2;
        const frac  = count === 0 ? 1 : 0.5 + 0.5 * (1 - n.total_tokens / maxTokens.value);
        const r     = R * (n.has_usage ? (0.6 + 0.4 * frac) : 1.05);
        return { ...n, x: CX + r * Math.cos(angle), y: CY + r * Math.sin(angle), radius: nodeRadius(n.total_tokens) };
    });
});

function selectNode(node) {
    selectedId.value = selectedId.value === node.id ? null : node.id;
    emit('select-agent', selectedId.value ? node : null);
}

function isTop(n) {
    return n.has_usage && n.total_tokens === maxTokens.value && maxTokens.value > 0;
}

function truncate(str, max = 10) {
    return str.length > max ? str.slice(0, max) + '…' : str;
}
</script>

<template>
    <div class="overflow-hidden rounded-[12px] border border-gray-100 bg-white shadow-[0_1px_3px_rgba(0,0,0,0.08)]">
        <!-- SVG area with faint grid bg -->
        <div class="relative" style="background:#FAFAFA">
            <svg class="absolute inset-0 h-full w-full" aria-hidden="true">
                <defs>
                    <pattern id="topo-grid" width="24" height="24" patternUnits="userSpaceOnUse">
                        <path d="M 24 0 L 0 0 0 24" fill="none" stroke="rgba(0,0,0,0.045)" stroke-width="1"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#topo-grid)" />
            </svg>

            <svg
                :viewBox="`0 0 ${WIDTH} ${HEIGHT}`"
                class="relative w-full"
                :style="{ maxHeight: '360px' }"
                aria-label="Agent topology graph"
            >
                <!-- Connection lines -->
                <line
                    v-for="n in positioned"
                    :key="'line-' + n.id"
                    :x1="CX" :y1="CY" :x2="n.x" :y2="n.y"
                    :stroke="isTop(n) ? '#f97316' : '#e5e7eb'"
                    stroke-width="1.5"
                    :stroke-dasharray="n.has_usage ? 'none' : '4 3'"
                    :opacity="n.has_usage ? 0.8 : 0.4"
                />

                <!-- Agent nodes -->
                <g
                    v-for="n in positioned"
                    :key="'node-' + n.id"
                    style="cursor: pointer"
                    role="button"
                    :aria-label="n.name"
                    @click="selectNode(n)"
                >
                    <!-- Top usage ring (orange) -->
                    <circle
                        v-if="isTop(n)"
                        :cx="n.x" :cy="n.y" :r="n.radius + 6"
                        fill="none" stroke="#f97316" stroke-width="2" opacity="0.7"
                    />
                    <!-- Selected ring -->
                    <circle
                        v-if="selectedId === n.id"
                        :cx="n.x" :cy="n.y" :r="n.radius + 9"
                        fill="none" stroke="#6366f1" stroke-width="1.5" opacity="0.5"
                    />
                    <!-- Node (white pill) -->
                    <circle
                        :cx="n.x" :cy="n.y" :r="n.radius"
                        fill="white"
                        :stroke="isTop(n) ? '#f97316' : (n.has_usage ? '#d1d5db' : '#f3f4f6')"
                        stroke-width="1.5"
                        :opacity="n.has_usage ? 1 : 0.5"
                        style="filter: drop-shadow(0 1px 3px rgba(0,0,0,0.10))"
                    />
                    <!-- Label -->
                    <text
                        :x="n.x" :y="n.y + 4"
                        text-anchor="middle"
                        font-size="8"
                        font-family="Inter, system-ui, sans-serif"
                        font-weight="500"
                        :fill="n.has_usage ? '#374151' : '#9ca3af'"
                        style="pointer-events: none; user-select: none"
                    >{{ truncate(n.name) }}</text>
                </g>

                <!-- Hub node -->
                <circle
                    :cx="CX" :cy="CY" r="34"
                    fill="white" stroke="#f97316" stroke-width="2"
                    style="filter: drop-shadow(0 2px 8px rgba(249,115,22,0.22))"
                />
                <text :x="CX" :y="CY - 4" text-anchor="middle" font-size="8" font-family="Inter, system-ui, sans-serif" fill="#f97316" font-weight="700">Token</text>
                <text :x="CX" :y="CY + 8" text-anchor="middle" font-size="7" font-family="Inter, system-ui, sans-serif" fill="#f97316" font-weight="500">Mgmt</text>
            </svg>
        </div>

        <!-- Legend -->
        <div class="flex flex-wrap gap-4 border-t border-gray-100 px-4 py-2.5 text-[11px] text-gray-400">
            <span class="flex items-center gap-1.5">
                <span class="h-2 w-2 rounded-full border border-gray-300 bg-white"></span> Agent
            </span>
            <span class="flex items-center gap-1.5">
                <span class="h-2 w-2 rounded-full border border-orange-400 bg-white"></span> Highest usage
            </span>
            <span class="flex items-center gap-1.5">
                <span class="h-2 w-2 rounded-full bg-gray-200"></span> No usage in range
            </span>
        </div>
    </div>

    <p v-if="nodes.length === 0" class="py-6 text-center text-[13px] text-gray-400">No agents found</p>
</template>
